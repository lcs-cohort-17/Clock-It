import React, { createContext, useContext, useEffect, useState } from 'react';
import { MdMenu } from 'react-icons/md';
import { ConnectionStatus } from './ConnectionStatus';


interface ThemeContextType {
  isDarkMode: boolean;
  toggleTheme: () => void;
}

const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

export const ThemeProvider = ({ children }: { children: React.ReactNode }) => {
  const [isDarkMode, setIsDarkMode] = useState<boolean>(() => {
    const saved = localStorage.getItem('theme');
    return saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
  });

  useEffect(() => {
    const root = window.document.documentElement;
    if (isDarkMode) {
      root.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      root.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    }
  }, [isDarkMode]);

  const toggleTheme = () => setIsDarkMode((prev) => !prev);

  return (
    <ThemeContext.Provider value={{ isDarkMode, toggleTheme }}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (context === undefined) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};


const ThemeToggle = () => {
  const { isDarkMode, toggleTheme } = useTheme();

  return (
    <div style={{
      display: 'inline-flex',
      alignItems: 'center',
      gap: '8px',                    
      userSelect: 'none',
      flexWrap: 'nowrap'             
    }}>
      <button 
        onClick={toggleTheme}
        style={{
          width: '44px',
          height: '22px',
          borderRadius: '9999px',
          border: isDarkMode ? '2px solid #f8fafc' : '2px solid #0f172a',
          background: 'transparent',      
          cursor: 'pointer',
          position: 'relative',
          padding: 0,
          display: 'flex',
          alignItems: 'center',
          transition: 'border-color 0.3s',
        }}
        aria-label="Toggle Theme"
      >
        <div style={{
          width: '14px',
          height: '14px',
          borderRadius: '50%',
          backgroundColor: isDarkMode ? '#f8fafc' : '#0f172a',
          position: 'absolute',
          left: isDarkMode ? '24px' : '2px',               
          transition: 'left 0.3s ease, background-color 0.3s',
        }} />
      </button>

      <div style={{ 
        display: 'flex', 
        alignItems: 'center',
        color: isDarkMode ? '#f8fafc' : '#0f172a', 
        transition: 'color 0.3s'
      }}>
        {isDarkMode ? (
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/>
            <path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
          </svg>
        ) : (
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
          </svg>
        )}
      </div>
    </div>
  );
};


type HeaderProps = {
  onMenuClick: () => void;
};

function Header({ onMenuClick }: HeaderProps) {
  return (
    /* We safely added dark:bg-[#050505] and dark:border-zinc-800 so the header bar turns dark smoothly! */
    <header className="border-b border-slate-200 bg-white px-4 py-4 md:px-8 transition-colors duration-300 dark:bg-[#050505] dark:border-zinc-800">
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
          <div className="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-[#093C5D] transition-colors duration-300 dark:bg-zinc-800 dark:text-[#facc15]">
            Staff
          </div>
        </div>

      </div>
    </header>
  );
}

// Wraps the Header with the Provider so the state works flawlessly inside it
export default function HeaderWithTheme(props: HeaderProps) {
  return (
    <ThemeProvider>
      <Header {...props} />
    </ThemeProvider>
  );
}