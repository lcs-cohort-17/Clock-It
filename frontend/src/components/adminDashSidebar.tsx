/* lutfeeya / adminDashboard */

import type { FC } from 'react';
import { NavLink } from 'react-router-dom';
import { LayoutDashboard, QrCode, FileText, Users, Settings, LogOut } from 'lucide-react';

interface SidebarProps {
  isOpen: boolean;
  onClose: () => void;
}

const Sidebar: FC<SidebarProps> = ({ isOpen, onClose }) => {
  const menuItems = [
    { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard, path: '/admin' },
    { id: 'qrcodes', label: 'QR Codes', icon: QrCode, path: '/qr-codes' },
    { id: 'logs', label: 'Attendance Logs', icon: FileText, path: '/attendance' },
    { id: 'users', label: 'User Management', icon: Users, path: '/users' },
    { id: 'settings', label: 'Settings', icon: Settings, path: '/settings' },
  ];

  return (
    <>
      {isOpen && (
        <div className="fixed inset-0 bg-black/50 z-40 lg:hidden" onClick={onClose} />
      )}

      <aside className={`
        fixed left-0 top-0 z-50 h-full w-64 bg-navy-dark text-white shadow-2xl border-r border-white/10
        transform transition-transform duration-300 ease-in-out lg:translate-x-0
        ${isOpen ? 'translate-x-0' : '-translate-x-full'}
      `}>
        <div className="flex flex-col h-full">
          {/* Logo */}
          <div className="flex items-center gap-3 px-6 py-6 border-b border-white/10">
            <div className="w-9 h-9 bg-white/10 border border-white/15 rounded-xl flex items-center justify-center">
              <span className="text-white font-bold text-lg">C</span>
            </div>
            <div>
              <span className="text-white font-bold text-xl">Clock It</span>
              <p className="text-white/60 text-xs">Admin</p>
            </div>
          </div>

          {/* Navigation */}
          <nav className="flex-1 p-4 space-y-1.5">
            {menuItems.map((item) => {
              const Icon = item.icon;
              return (
                <NavLink
                  key={item.id}
                  to={item.path}
                  className={({ isActive }) =>
                    `w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 ${
                      isActive
                        ? 'bg-white/10 text-white shadow-inner'
                        : 'text-white/70 hover:bg-white/10 hover:text-white'
                    }`
                  }
                  onClick={onClose}
                >
                  <Icon className="w-5 h-5" />
                  {item.label}
                </NavLink>
              );
            })}
          </nav>

          {/* User Footer */}
          <div className="p-4 border-t border-white/10">
            <div className="flex items-start gap-3">
              <div className="w-10 h-10 rounded-full bg-blue flex items-center justify-center">
                <span className="text-white font-semibold text-sm">A</span>
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-white text-sm font-medium truncate">Admin</p>
                <p className="text-white/60 text-xs truncate">admin@clockit.app</p>
                <button className="mt-3 inline-flex items-center gap-2 text-white/60 hover:text-white transition">
                  <LogOut className="w-4 h-4" />
                  <span className="text-sm font-medium">Log out</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </aside>
    </>
  );
};

export default Sidebar;

/* lutfeeya / adminDashboard */