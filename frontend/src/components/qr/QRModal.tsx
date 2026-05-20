import { useEffect, useState } from "react";
import QRCode from "react-qr-code";
import { generateQRToken } from "./qrService";


type QRType = "clock-in" | "clock-out";

type Props = {
  open: boolean;
  onClose: () => void;
  type: QRType;
};

export default function QRModal({ open, onClose, type }: Props) {
  const [token, setToken] = useState("");
  const [loading, setLoading] = useState(false);
  const [expired, setExpired] = useState(false);
  const [timeLeft, setTimeLeft] = useState(60);
  const [error, setError] = useState("");

  const fetchQRCode = async () => {
    try {
      setLoading(true);
      setError("");
      setExpired(false);
      const data = await generateQRToken(type);
      setToken(data.token);
      setTimeLeft(data.expiresIn);
    } catch {
      setError("Failed to generate QR code");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (open) fetchQRCode();
    else { setToken(""); setExpired(false); setError(""); }
  }, [open, type]);

  useEffect(() => {
    if (!token || expired) return;
    const timer = setInterval(() => {
      setTimeLeft((prev) => {
        if (prev <= 1) { clearInterval(timer); setExpired(true); return 0; }
        return prev - 1;
      });
    }, 1000);
    return () => clearInterval(timer);
  }, [token, expired]);

  useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => { if (e.key === "Escape") onClose(); };
    window.addEventListener("keydown", handleEscape);
    return () => window.removeEventListener("keydown", handleEscape);
  }, [onClose]);

  if (!open) return null;

  return (
    <div
      className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
      role="dialog"
      aria-modal="true"
      onClick={onClose}
    >
      <div
        className="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-xl font-semibold capitalize">
            {type.replace("-", " ")} QR
          </h2>
          <button
            onClick={onClose}
            aria-label="Close Modal"
            className="text-gray-500 hover:text-black text-2xl leading-none"
          >×</button>
        </div>

        <div className="flex flex-col items-center justify-center min-h-[280px]">
          {loading && <p className="text-gray-500 py-10">Generating QR Code...</p>}

          {error && (
            <div className="text-center">
              <p className="text-red-500 mb-4">{error}</p>
              <button onClick={fetchQRCode} className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Retry</button>
            </div>
          )}

          {!loading && !error && expired && (
            <div className="text-center">
              <p className="text-red-500 font-medium mb-4">QR Code Expired</p>
              <button onClick={fetchQRCode} className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Generate New QR</button>
            </div>
          )}

          {!loading && !error && !expired && token && (
            <>
              <div className="bg-white p-4 rounded-xl">
                <QRCode value={token} size={220} />
              </div>
              <p className="mt-4 text-sm text-gray-500">Expires in {timeLeft}s</p>
              <button onClick={fetchQRCode} className="mt-5 bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg transition">Refresh QR</button>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
