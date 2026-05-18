import {
  MdCalendarMonth,
  MdPerson,
  MdQrCodeScanner,
  MdRequestQuote,
} from 'react-icons/md'

function DashboardGrid() {
  return (
    <section className="flex-1 bg-[#F5F5F5] p-8">
      {/* TOP GRID */}
      <div className="rounded-2xl bg-white p-8 shadow-sm">
        <div className="flex items-start justify-between gap-6">
          {/* LEFT SIDE */}
          <div>
            <p className="text-xs font-bold uppercase tracking-wide text-slate-400">
              Current Status
            </p>

            <h1 className="mt-2 text-4xl font-bold text-[#093C5D]">
              Clocked Out
            </h1>

            <p className="mt-2 inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">
              You are currently OFFSITE
            </p>

            <p className="mt-4 text-sm text-slate-500">
              No actions yet today.
            </p>
          </div>

          {/* RIGHT SIDE */}
          <h2 className="mt-6 text-5xl font-bold text-[#093C5D]">
            12:34 PM
          </h2>
        </div>

        <button className="mt-8 flex w-full items-center justify-center gap-2 rounded-xl bg-[#9CB07A] py-4 text-base font-bold text-[#093C5D] shadow-sm">
          <MdQrCodeScanner size={22} />
          Scan QR Code to Clock In
        </button>

        <p className="mt-3 text-center text-xs text-slate-400">
          Scan the QR at your site. Works offline — syncs later.
        </p>
      </div>

      {/* BOTTOM GRID */}
      <div className="mt-8 grid gap-6 md:grid-cols-3">
        <div className="rounded-2xl bg-white p-6 font-semibold text-[#093C5D] shadow-sm">
          <div className="mb-5 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
            <MdCalendarMonth size={22} />
          </div>

          <h3>Calendar</h3>

          <p className="mt-2 text-sm font-normal text-slate-500">
            See your monthly attendance.
          </p>
        </div>

        <div className="rounded-2xl bg-white p-6 font-semibold text-[#093C5D] shadow-sm">
          <div className="mb-5 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
            <MdRequestQuote size={22} />
          </div>

          <h3>Leave Requests</h3>

          <p className="mt-2 text-sm font-normal text-slate-500">
            Submit and track applications.
          </p>
        </div>

        <div className="rounded-2xl bg-white p-6 font-semibold text-[#093C5D] shadow-sm">
          <div className="mb-5 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
            <MdPerson size={22} />
          </div>

          <h3>Profile</h3>

          <p className="mt-2 text-sm font-normal text-slate-500">
            Manage your personal details.
          </p>
        </div>
      </div>
    </section>
  )
}

export default DashboardGrid