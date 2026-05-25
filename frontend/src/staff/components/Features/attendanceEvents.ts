export type AttendanceScanType = 'clock-in' | 'clock-out'

export interface AttendanceScanEvent {
  date: string
  iso: string
  time: string
  type: AttendanceScanType
}

const STORAGE_KEY = 'attendanceEvents'
export const ATTENDANCE_EVENTS_UPDATED = 'attendance-events-updated'

export function formatDateKey(date: Date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export function formatScanTime(date: Date) {
  return date.toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatTimeDifference(totalMinutes: number) {
  const safeMinutes = Math.max(0, Math.floor(totalMinutes))
  return `${safeMinutes} ${safeMinutes === 1 ? 'minute' : 'minutes'}`
}

export function getAttendanceScanEvents(): AttendanceScanEvent[] {
  return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]')
}

export function recordAttendanceScan(type: AttendanceScanType, scannedAt = new Date()) {
  const event: AttendanceScanEvent = {
    type,
    iso: scannedAt.toISOString(),
    date: formatDateKey(scannedAt),
    time: formatScanTime(scannedAt),
  }
  const events = [...getAttendanceScanEvents(), event]
  localStorage.setItem(STORAGE_KEY, JSON.stringify(events))
  window.dispatchEvent(new CustomEvent(ATTENDANCE_EVENTS_UPDATED, { detail: event }))
  return event
}

export function getTodaysScanEvents(date = new Date()) {
  const today = formatDateKey(date)
  return getAttendanceScanEvents().filter(event => event.date === today)
}

export function getLatestScanEvent() {
  return [...getAttendanceScanEvents()].sort(
    (a, b) => new Date(b.iso).getTime() - new Date(a.iso).getTime()
  )[0]
}

export function getTodaysActivity(date = new Date()) {
  const todaysEvents = getTodaysScanEvents(date)
  const firstClockIn = todaysEvents.find(event => event.type === 'clock-in')
  const lastClockOut = [...todaysEvents].reverse().find(event => event.type === 'clock-out')

  if (!firstClockIn) {
    return null
  }

  const totalMinutes =
    lastClockOut
      ? Math.max(
          0,
          (new Date(lastClockOut.iso).getTime() - new Date(firstClockIn.iso).getTime()) /
            (1000 * 60)
        )
      : 0

  return {
    firstClockIn: firstClockIn.time,
    lastClockOut: lastClockOut?.time ?? 'Not clocked out yet',
    totalHours: formatTimeDifference(totalMinutes),
  }
}
