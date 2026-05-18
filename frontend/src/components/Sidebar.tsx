import {
  MdDashboard,
  MdPerson,
  MdQrCodeScanner,
  MdHistory,
  MdLogout,
} from 'react-icons/md'

type SidebarProps = {
  activeView: 'dashboard' | 'profile'
  onDashboardClick: () => void
  onProfileClick: () => void
}

function Sidebar({ activeView, onDashboardClick, onProfileClick }: SidebarProps) {
  const getNavButtonClassName = (isActive: boolean) =>
    [
      'flex items-center gap-3 rounded-lg px-4 py-3 text-left transition',
      isActive ? 'bg-white text-[#093C5D]' : 'text-white hover:bg-white hover:text-[#093C5D]',
    ].join(' ')

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
        <button
          className={getNavButtonClassName(activeView === 'dashboard')}
          onClick={onDashboardClick}
          type="button"
        >
          <MdDashboard size={22} />
          Dashboard
        </button>

        <button
          className={getNavButtonClassName(false)}
          type="button"
        >
          <MdQrCodeScanner size={22} />
          Scan QR Code
        </button>

        <button
          className={getNavButtonClassName(false)}
          type="button"
        >
          <MdHistory size={22} />
          Attendance History
        </button>

        <button
          className={getNavButtonClassName(activeView === 'profile')}
          onClick={onProfileClick}
          type="button"
        >
          <MdPerson size={22} />
          Profile
        </button>
      </nav>

      <button className="mt-auto flex items-center justify-center gap-3 rounded-lg bg-slate-900 px-4 py-3 text-white transition hover:bg-slate-700">
        <MdLogout size={22} />
        Sign Out
      </button>
    </aside>
  )
}

export default Sidebar
