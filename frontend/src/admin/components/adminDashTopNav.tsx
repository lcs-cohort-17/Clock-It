/* lutfeeya / adminDashboard */

import type { FC } from 'react';
import { Menu, Wifi } from 'lucide-react';
import { ThemeToggle } from '../../components/ThemeToggle';

interface TopNavProps {
  onMenuClick: () => void;
}

const TopNav: FC<TopNavProps> = ({ onMenuClick }) => {
  return (
    <header className="sticky top-0 z-30 border-b border-gray-200 bg-white transition-colors duration-300 dark:border-[#1b3f6d] dark:bg-[#081a2f]">
      <div className="flex items-center justify-between px-4 py-3 lg:px-6">
        <button
          onClick={onMenuClick}
          className="rounded-lg p-2 text-navy transition hover:bg-gray-100 dark:text-white"
        >
          <Menu className="w-5 h-5" />
        </button>

        <div className="flex-1" />

        <div className="flex items-center gap-3">
          <ThemeToggle />

          <div className="flex items-center gap-2">
            <Wifi className="w-4 h-4 text-olive" />
            <span className="text-olive text-sm hidden sm:inline">Online</span>
          </div>
          
          <div className="inline-flex h-8 items-center rounded-full bg-gray-100 px-3 text-sm font-medium text-gray-600 transition-colors duration-300 dark:bg-[#164068] dark:text-[#eff6ff]">
            Admin
          </div>
        </div>
      </div>
    </header>
  );
};

export default TopNav;

/* lutfeeya / adminDashboard */
