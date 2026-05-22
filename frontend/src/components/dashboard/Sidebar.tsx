import {
  MdDashboard,
  MdPerson,
  MdQrCodeScanner,
  MdHistory,
  MdLogout,
  MdAccessTime,
} from 'react-icons/md'

import { NavLink } from 'react-router-dom'

type SidebarProps = {
  isOpen?: boolean
  onClose?: () => void
  user: {
    fullName: string
    email: string
    role: string
  }
}

function Sidebar({
  isOpen = false,
  onClose,
  user,
}: SidebarProps) {

  // Shared styles for all nav links
  const navLinkClasses = ({ isActive }: { isActive: boolean }) =>
    `flex items-center gap-4 rounded-2xl px-4 py-4 text-base font-semibold no-underline transition ${
      isActive
        ? 'bg-[#3B7597] text-white'
        : 'text-white hover:bg-[#3B7597]'
    }`

  return (
    <aside
      className={`fixed left-0 top-0 z-50 flex h-screen w-72 flex-col bg-[#093C5D] text-white transition-transform duration-300 md:static md:translate-x-0 ${
        isOpen ? 'translate-x-0' : '-translate-x-full'
      }`}
    >

      {/* HEADER */}
      <div className="border-b border-white/10 px-5 py-4">

        {/* MOBILE CLOSE BUTTON */}
        <div className="mb-2 flex justify-end md:hidden">
          <button
            onClick={onClose}
            className="text-2xl text-white"
          >
            ×
          </button>
        </div>

        {/* LOGO */}
        <div className="flex items-center gap-2">

          <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#3B7597]">
            <MdAccessTime
              size={24}
              className="text-white"
            />
          </div>

          <div>
            <h1 className="text-2xl font-semibold text-white">
              Clock It
            </h1>

            <p className="text-sm font-normal tracking-wide text-white/70">
              Attendance suite
            </p>
          </div>

        </div>
      </div>

      {/* NAVIGATION */}
      <nav className="mt-6 flex flex-col gap-2 px-4">

        <NavLink
          to="/staff-dashboard"
          className={navLinkClasses}
          onClick={onClose}
        >
          <MdDashboard
            size={22}
            className="text-white"
          />

          <span>Dashboard</span>
        </NavLink>

        <NavLink
          to="/scan-qr"
          className={navLinkClasses}
          onClick={onClose}
        >
          <MdQrCodeScanner
            size={22}
            className="text-white"
          />

          <span>Scan QR</span>
        </NavLink>

        <NavLink
          to="/history"
          className={navLinkClasses}
          onClick={onClose}
        >
          <MdHistory
            size={22}
            className="text-white"
          />

          <span>History</span>
        </NavLink>

        <NavLink
          to="/profile"
          className={navLinkClasses}
          onClick={onClose}
        >
          <MdPerson
            size={22}
            className="text-white"
          />

          <span>Profile</span>
        </NavLink>

      </nav>

      {/* FOOTER */}
      <div className="mt-auto border-t border-white/10 px-7 py-6">

        <div className="mb-6">
          <h2 className="text-lg font-semibold text-white">
            {user.fullName}
          </h2>

          <p className="text-sm text-white/70">
            {user.email}
          </p>
        </div>

        <NavLink
          to="/"
          onClick={onClose}
          className="flex items-center gap-3 text-lg font-semibold no-underline transition hover:opacity-80"
        >
          <MdLogout
            size={22}
            className="text-white"
          />

          <span className="text-white">
            Log out
          </span>
        </NavLink>

      </div>
    </aside>
  )
}

export default Sidebar
