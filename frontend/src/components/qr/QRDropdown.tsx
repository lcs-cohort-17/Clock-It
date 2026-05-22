import { useEffect, useRef, useState } from "react";
import QRModal from "./QRModal";

type QRType = "clock-in" | "clock-out";

export default function QRDropdown() {
  const [open, setOpen] = useState(false);
  const [type, setType] = useState<QRType>("clock-in");
  const [showDropdown, setShowDropdown] = useState(false);
  const wrapperRef = useRef<HTMLDivElement>(null);

  const handleOpen = (selectedType: QRType) => {
    setType(selectedType);
    setOpen(true);
    setShowDropdown(false);
  };

  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (wrapperRef.current && !wrapperRef.current.contains(e.target as Node)) {
        setShowDropdown(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  return (
    <div className="relative inline-block" ref={wrapperRef}>
      <button
        onClick={() => setShowDropdown((prev) => !prev)}
        className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
        aria-haspopup="true"
        aria-expanded={showDropdown}
      >
        QR Options
      </button>

      {showDropdown && (
        <div className="absolute mt-2 w-48 bg-white rounded-lg shadow-lg border overflow-hidden z-20">
          <button onClick={() => handleOpen("clock-in")} className="w-full text-left px-4 py-3 hover:bg-gray-100">Clock In QR</button>
          <button onClick={() => handleOpen("clock-out")} className="w-full text-left px-4 py-3 hover:bg-gray-100">Clock Out QR</button>
        </div>
      )}

      <QRModal open={open} onClose={() => setOpen(false)} type={type} />
    </div>
  );
}
