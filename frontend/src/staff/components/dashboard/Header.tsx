import { MdMenu } from 'react-icons/md';
import { ConnectionStatus } from './ConnectionStatus';
import { ThemeToggle } from '../../../components/ThemeToggle';

type HeaderProps = {
  onMenuClick: () => void;
};

function Header({ onMenuClick }: HeaderProps) {
  return (
    /* We safely added dark:bg-[#081a2f] and dark:border-[#1b3f6d] so the header bar turns dark smoothly!!! */
    <header className="border-b border-slate-200 bg-white px-4 py-4 md:px-8 transition-colors duration-300 dark:bg-[#081a2f] dark:border-[#1b3f6d]">
      <div className="flex items-center justify-between">

        {/* LEFT SIDE */}
        <div className="flex items-center gap-3">
          <button
            onClick={onMenuClick}
            className="text-[#093C5D] dark:text-white md:hidden"
          >
            <MdMenu size={28} />
          </button>
        </div>

        {/* RIGHT SIDE */}
        <div className="flex items-center justify-end gap-4">
          <ThemeToggle />
          <ConnectionStatus />

          {/* Added dark configuration so your staff badge looks perfect in dark layout */}
          <div className="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-[#093C5D] transition-colors duration-300 dark:bg-[#164068] dark:text-[#eff6ff]">
            Staff
          </div>
        </div>

      </div>
    </header>
  );
}

export default Header;
