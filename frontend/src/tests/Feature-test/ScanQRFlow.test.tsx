import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import {
  getTodaysActivity,
  recordAttendanceScan,
  type AttendanceScanType,
} from '../../components/Features/attendanceEvents'
import attendanceMock from './attendanceMock.json'

const scanCodeToType: Record<string, AttendanceScanType> = {
  [attendanceMock.scanCodes.clockIn]: 'clock-in',
  [attendanceMock.scanCodes.clockOut]: 'clock-out',
}

describe('Scan QR clock-in/out mock data', () => {
  beforeEach(() => {
    localStorage.clear()
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  test('stores a clock-in event from the mock CLOCK_IN code', () => {
    const event = recordAttendanceScan(
      scanCodeToType[attendanceMock.scanCodes.clockIn],
      new Date(attendanceMock.attendanceEvents[0].iso)
    )

    expect(event).toEqual(
      expect.objectContaining({
        date: attendanceMock.today,
        type: 'clock-in',
      })
    )
    expect(JSON.parse(localStorage.getItem('attendanceEvents') ?? '[]')).toHaveLength(1)
  })

  test('stores a clock-out event from the mock CLOCK_OUT code', () => {
    const event = recordAttendanceScan(
      scanCodeToType[attendanceMock.scanCodes.clockOut],
      new Date(attendanceMock.attendanceEvents[1].iso)
    )

    expect(event).toEqual(
      expect.objectContaining({
        date: attendanceMock.today,
        type: 'clock-out',
      })
    )
    expect(JSON.parse(localStorage.getItem('attendanceEvents') ?? '[]')).toHaveLength(1)
  })

  test('builds today activity from mock clock-in and clock-out events', () => {
    localStorage.setItem('attendanceEvents', JSON.stringify(attendanceMock.attendanceEvents))

    expect(getTodaysActivity(new Date(`${attendanceMock.today}T12:00:00.000Z`))).toEqual(
      attendanceMock.todaysActivity
    )
  })
})
