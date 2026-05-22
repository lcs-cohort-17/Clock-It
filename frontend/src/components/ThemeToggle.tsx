import React from 'react';
import { useTheme } from '../context/ThemeContext.tsx'; 

export function ThemeToggle() { 
  const { isDarkMode, toggleTheme } = useTheme();

  return (
    <div style={{
      display: 'inline-flex',
      alignItems: 'center',
      gap: '12px',
      userSelect: 'none',
      flexWrap: 'nowrap'
    }}>
      
      {/* Sliding Switch Container */}
      <button 
        onClick={toggleTheme}
        style={{
          width: '44px',
          height: '22px',
          borderRadius: '9999px',
          border: isDarkMode ? '2px solid #0f172a' : '2px solid #0f172a',
          background: 'transparent',
          cursor: 'pointer',
          position: 'relative',
          padding: 0,
          display: 'flex',
          alignItems: 'center'
        }}
        aria-label="Toggle Theme"
      >
        {/* Moving Slider Circle */}
        <div style={{
          width: '14px',
          height: '14px',
          borderRadius: '50%',
          backgroundColor: '#0f172a',
          position: 'absolute',
          left: isDarkMode ? '24px' : '2px',
          transition: 'left 0.2s ease, background-color 0.2s',
        }} />
      </button>

      {/* See-Through Black & White Adaptive Icon */}
      <div style={{ 
        display: 'flex', 
        alignItems: 'center',
        color: '#0f172a',
        transition: 'color 0.2s'
      }}>
        {isDarkMode ? (
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
          </svg>
        ) : (
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/>
            <path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
          </svg>
        )}
      </div>

    </div>
  );
}