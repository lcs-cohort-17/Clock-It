/* lutfeeya / adminDashboard */

import type { FC } from 'react';
import { Menu, Wifi } from 'lucide-react';

interface TopNavProps {
  onMenuClick: () => void;
}

const TopNav: FC<TopNavProps> = ({ onMenuClick }) => {
  return (
    <header className="bg-white border-b border-gray-200 sticky top-0 z-30">
      <div className="flex items-center justify-between px-4 py-3 lg:px-6">
        <button
          onClick={onMenuClick}
          className="lg:hidden text-navy p-2 hover:bg-gray-100 rounded-lg transition"
        >
          <Menu className="w-5 h-5" />
        </button>

        <div className="flex-1" />

        <div className="flex items-center gap-3">
          <div className="flex items-center gap-2">
            <Wifi className="w-4 h-4 text-olive" />
            <span className="text-olive text-sm hidden sm:inline">Online</span>
          </div>
          
          <div className="inline-flex h-8 items-center rounded-full bg-gray-100 px-3 text-sm font-medium text-gray-600">
            Admin
          </div>
        </div>
      </div>
    </header>
  );
};

export default TopNav;

/* lutfeeya / adminDashboard */