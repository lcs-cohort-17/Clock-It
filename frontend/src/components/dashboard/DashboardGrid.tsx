import { useEffect, useState, type ReactNode } from 'react'
import { useNavigate } from 'react-router-dom'
import {
  MdAccessTime,
  MdCalendarMonth,
  MdClose,
  MdDescription,
  MdLocationOn,
  MdQrCodeScanner,
} from 'react-icons/md'
import Calendar, { type AttendanceEvent } from '../Features/Calendar'
import {
  getAttendanceScanEvents,
  getLatestScanEvent,
  getTodaysActivity,
} from '../Features/attendanceEvents'
import LeaveModal, { type LeaveRequest } from '../Features/LeaveModal'
import TodaysActivity from '../Features/TodaysActivity'

type Props = {
  user?:{
    fullName: string
  }
}
const defaultUser = {
  fullName: 'Sarah Mthembu',
}

function DashboardGrid({ user = defaultUser }: Props) {
  const navigate = useNavigate()
  const [isLeaveModalOpen, setIsLeaveModalOpen] = useState(false)
  const [isCalendarModalOpen, setIsCalendarModalOpen] = useState(false)
  const [currentTime, setCurrentTime] = useState(() =>
    new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  )
  const latestScan = getLatestScanEvent()
  const isClockedIn = latestScan?.type === 'clock-in'
  const todaysActivity = getTodaysActivity()
  const attendanceEvents: AttendanceEvent[] = getAttendanceScanEvents().map(event => ({
    date: event.date,
    type: event.type,
    time: event.time,
  }))

  const handleLeaveSubmit = (request: LeaveRequest) => {
    console.log('Leave request saved for admin review', request)
  }

  useEffect(() => {
    const timer = window.setInterval(() => {
      setCurrentTime(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }))
    }, 30000)

    return () => window.clearInterval(timer)
  }, [])

  return (
    <section className="flex-1 bg-[#F5F5F5] p-4 md:p-8">
      <div className="rounded-2xl bg-white p-5 shadow-sm md:p-8">
        <div className="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
          <div>
            <p className="text-xs font-bold uppercase tracking-wide text-slate-400">
            Good morning, {user.fullName.split(' ')[0]}
            </p>

            <h1 className="mt-2 text-3xl font-bold text-[#093C5D] md:text-5xl">
              {isClockedIn ? 'Clocked In' : 'Clocked Out'}
            </h1>

            <p className="mt-3 inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">
              You are currently {isClockedIn ? 'ONSITE' : 'OFFSITE'}
            </p>

            <p className="mt-4 text-sm text-slate-500">
              {latestScan
                ? `Last scan: ${latestScan.time}`
                : 'No QR scan recorded yet.'}
            </p>
          </div>

          <div className="text-left md:text-right">
            <h2 className="text-4xl font-bold text-[#093C5D] md:text-6xl">
              {currentTime}
            </h2>
          </div>
        </div>

        <div className="mt-8">
          <button
            type="button"
            onClick={() => navigate('/scan-qr')}
            className="flex w-full items-center justify-center gap-2 rounded-xl bg-[#093C5D] py-4 text-sm font-bold text-white shadow-sm transition hover:opacity-90 md:text-base"
          >
            <MdQrCodeScanner size={22} />
            Scan QR
          </button>
        </div>

        <p className="mt-3 text-center text-xs text-slate-400">
          Scan the QR at your site. Works offline and syncs later.
        </p>
      </div>

      <div className="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <div className="mb-6 flex items-center gap-2 text-[#093C5D]">
          <MdLocationOn size={22} />
          <h2 className="text-xl font-bold">Today's activity</h2>
        </div>
        <TodaysActivity activity={todaysActivity} />
      </div>

      <div className="mt-8 grid gap-5 md:grid-cols-3">
        <DashboardActionCard
          icon={<MdCalendarMonth size={30} />}
          title="Calendar"
          description="View your schedule"
          onClick={() => setIsCalendarModalOpen(true)}
        />
        <DashboardActionCard
          icon={<MdDescription size={30} />}
          title="Leave Requests"
          description="Submit a new request"
          ariaLabel="Request Leave/Sick"
          onClick={() => setIsLeaveModalOpen(true)}
        />
        <DashboardActionCard
          icon={<MdAccessTime size={30} />}
          title="Profile"
          description="Manage your account"
          onClick={() => navigate('/profile')}
        />
      </div>

      {isCalendarModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4">
          <div className="w-full max-w-[640px] rounded-2xl bg-white p-8 shadow-2xl">
            <div className="mb-8 flex items-start justify-between gap-6">
              <div>
                <h2 className="text-2xl font-bold text-[#093C5D]">Calendar</h2>
                <p className="mt-1 text-lg text-[#3B5C74]">Pick a date to view your schedule.</p>
              </div>
              <button
                type="button"
                onClick={() => setIsCalendarModalOpen(false)}
                className="flex h-9 w-9 items-center justify-center rounded-full text-[#3B5C74] transition hover:bg-slate-100"
                aria-label="Close calendar"
              >
                <MdClose size={24} />
              </button>
            </div>
            <Calendar events={attendanceEvents} />
          </div>
        </div>
      )}

      <LeaveModal
        isOpen={isLeaveModalOpen}
        onClose={() => setIsLeaveModalOpen(false)}
        onSubmit={handleLeaveSubmit}
      />
    </section>
  )
}

type DashboardActionCardProps = {
  icon: ReactNode
  title: string
  description: string
  ariaLabel?: string
  onClick: () => void
}

function DashboardActionCard({ icon, title, description, ariaLabel, onClick }: DashboardActionCardProps) {
  return (
    <button
      type="button"
      aria-label={ariaLabel}
      onClick={onClick}
      className="min-h-36 rounded-2xl border border-slate-200 bg-white p-6 text-left text-[#093C5D] shadow-sm transition hover:-translate-y-0.5 hover:border-[#9CB07A] hover:shadow-md"
    >
      <div className="mb-5 text-[#093C5D]">{icon}</div>
      <h3 className="text-xl font-bold">{title}</h3>
      <p className="mt-2 text-base text-[#16425F]">{description}</p>
    </button>
  )
}

export default DashboardGrid
