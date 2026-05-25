import { Moon, Sun } from 'lucide-react';
import { useTheme } from '../context/ThemeContext';

export function ThemeToggle() {
  const { isDarkMode, toggleTheme } = useTheme();
  const iconColor = isDarkMode ? '#f8fafc' : '#0f172a';

  return (
    <div
      style={{
        display: 'inline-flex',
        alignItems: 'center',
        gap: '8px',
        userSelect: 'none',
        flexWrap: 'nowrap',
      }}
    >
      <button
        onClick={toggleTheme}
        style={{
          width: '44px',
          height: '22px',
          borderRadius: '9999px',
          border: `2px solid ${iconColor}`,
          background: 'transparent',
          cursor: 'pointer',
          position: 'relative',
          padding: 0,
          display: 'flex',
          alignItems: 'center',
          transition: 'border-color 0.3s',
        }}
        aria-label="Toggle theme"
      >
        <div
          style={{
            width: '14px',
            height: '14px',
            borderRadius: '50%',
            backgroundColor: iconColor,
            position: 'absolute',
            left: isDarkMode ? '24px' : '2px',
            transition: 'left 0.3s ease, background-color 0.3s',
          }}
        />
      </button>

      <div
        style={{
          display: 'flex',
          alignItems: 'center',
          color: iconColor,
          transition: 'color 0.3s',
        }}
      >
        {isDarkMode ? <Moon size={20} strokeWidth={2.5} /> : <Sun size={20} strokeWidth={2.5} />}
      </div>
    </div>
  );
}
