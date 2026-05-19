import {
  MdDashboard,
  MdPerson,
  MdQrCodeScanner,
  MdHistory,
  MdLogout,
} from 'react-icons/md'

function Sidebar() {
  return (
    <aside className="flex min-h-screen w-64 flex-col border-r border-slate-200 bg-[#093C5D] p-6">
      <div>
        <h1 className="text-3xl font-bold text-white">
          Clock-It
        </h1>

        <p className="mt-1 text-sm text-white">
          Attendance System.
        </p>
      </div>

      <nav className="mt-10 flex flex-col gap-3">
        <a
          href="#"
          className="flex items-center gap-3 rounded-lg px-4 py-3 text-white transition hover:bg-white hover:text-[#093C5D]"
        >
          <MdDashboard size={22} />
          Dashboard
        </a>

        <a
          href="#"
          className="flex items-center gap-3 rounded-lg px-4 py-3 text-white transition hover:bg-white hover:text-[#093C5D]"
        >
          <MdQrCodeScanner size={22} />
          Scan QR Code
        </a>

        <a
          href="#"
          className="flex items-center gap-3 rounded-lg px-4 py-3 text-white transition hover:bg-white hover:text-[#093C5D]"
        >
          <MdHistory size={22} />
          Attendance History
        </a>

        <a
          href="#"
          className="flex items-center gap-3 rounded-lg px-4 py-3 text-white transition hover:bg-white hover:text-[#093C5D]"
        >
          <MdPerson size={22} />
          Profile
        </a>
      </nav>

      <button className="mt-auto flex items-center justify-center gap-3 rounded-lg bg-slate-900 px-4 py-3 text-white transition hover:bg-slate-700">
        <MdLogout size={22} />
        Sign Out
      </button>
    </aside>
  )
}

export default Sidebar