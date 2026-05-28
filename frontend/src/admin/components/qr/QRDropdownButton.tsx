import React, { useState, useRef, useEffect } from 'react';
import { Clock, LogIn, LogOut, ChevronDown } from 'lucide-react';
import type { QRType } from '../hooks/useQRCode';

interface QRDropdownButtonProps {
  onSelect: (type: QRType) => void;
}

const QRDropdownButton: React.FC<QRDropdownButtonProps> = ({ onSelect }) => {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);
  
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);
  
  const handleSelect = (type: QRType) => {
    onSelect(type);
    setIsOpen(false);
  };
  
  return (
    <div className="relative" ref={dropdownRef}>
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="inline-flex items-center gap-2 px-5 py-2.5 bg-[#002f4f] text-white rounded-lg font-medium shadow-sm hover:bg-[#003f5f] transition-all focus:outline-none focus:ring-2 focus:ring-[#002f4f]/50"
        aria-haspopup="true"
        aria-expanded={isOpen}
        aria-label="Open QR code options"
      >
        <Clock className="w-4 h-4" />
        Generate QR Code
        <ChevronDown className={`w-4 h-4 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
      </button>
      
      {isOpen && (
        <div 
          className="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-20 animate-in fade-in slide-in-from-top-2 duration-200"
          role="menu"
        >
          <button
            onClick={() => handleSelect('clock-in')}
            className="w-full flex items-center gap-3 px-4 py-2.5 text-left text-gray-700 hover:bg-gray-50 transition-colors rounded-t-lg"
            role="menuitem"
          >
            <LogIn className="w-4 h-4 text-green-600" />
            <span>Clock In QR</span>
          </button>
          <button
            onClick={() => handleSelect('clock-out')}
            className="w-full flex items-center gap-3 px-4 py-2.5 text-left text-gray-700 hover:bg-gray-50 transition-colors rounded-b-lg"
            role="menuitem"
          >
            <LogOut className="w-4 h-4 text-amber-600" />
            <span>Clock Out QR</span>
          </button>
        </div>
      )}
    </div>
  );
};

export default QRDropdownButton;