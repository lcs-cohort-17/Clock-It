import { useState } from 'react'
import { MdCalendarMonth, MdRequestQuote } from 'react-icons/md'
import Calendar, { type AttendanceEvent } from '../components/Features/Calendar'
import LeaveModal, { type LeaveRequest } from '../components/Features/LeaveModal'
import { getAttendanceScanEvents } from '../components/Features/attendanceEvents'

function CalendarPage() {
  const [isLeaveModalOpen, setIsLeaveModalOpen] = useState(false)

  const attendanceEvents: AttendanceEvent[] = getAttendanceScanEvents().map(event => ({
    date: event.date,
    type: event.type,
    time: event.time,
  }))

  const handleLeaveSubmit = (request: LeaveRequest) => {
    console.log('Leave request saved for admin review', request)
  }

  return (
    <section className="flex-1 bg-[#F5F5F5] p-4 md:p-8">
      <div className="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <div className="mb-2 flex items-center gap-2 text-[#093C5D]">
            <MdCalendarMonth size={24} />
            <h1 className="text-2xl font-bold md:text-3xl">Calendar</h1>
          </div>
          <p className="text-sm text-slate-500">
            Attendance days are marked in green from your saved QR scan records.
          </p>
        </div>

        <button
          type="button"
          onClick={() => setIsLeaveModalOpen(true)}
          className="inline-flex items-center justify-center gap-2 rounded-xl bg-[#9CB07A] px-5 py-3 text-sm font-bold text-[#093C5D] transition hover:opacity-90"
        >
          <MdRequestQuote size={20} />
          Request Leave/Sick
        </button>
      </div>

      <div className="rounded-2xl bg-white p-4 shadow-sm md:p-6">
        <Calendar events={attendanceEvents} />
      </div>

      <LeaveModal
        isOpen={isLeaveModalOpen}
        onClose={() => setIsLeaveModalOpen(false)}
        onSubmit={handleLeaveSubmit}
      />
    </section>
  )
}

export default CalendarPage
