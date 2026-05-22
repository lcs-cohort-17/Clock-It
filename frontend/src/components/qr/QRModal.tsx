import React, { useState, useEffect } from 'react';
import { QRCodeCanvas } from 'qrcode.react';
import { X, RefreshCw, AlertCircle, CheckCircle, QrCode, Loader2 } from 'lucide-react';
import type { QRType} from '../hooks/useQRCode';
import { useQRCode } from '../hooks/useQRCode';

interface QRModalProps {
  isOpen: boolean;
  onClose: () => void;
  qrType: QRType | null;
}

const QRModal: React.FC<QRModalProps> = ({ isOpen, onClose, qrType }) => {
  const { qrData, status, errorMessage, generateQR, reset } = useQRCode();
  const [timeLeft, setTimeLeft] = useState<number>(0);
  
  // Generate QR when modal opens with a type
  useEffect(() => {
    if (isOpen && qrType) {
      generateQR(qrType);
    }
    return () => {
      reset();
    };
  }, [isOpen, qrType, generateQR, reset]);
  
  // Countdown timer
  useEffect(() => {
    if (!qrData || status !== 'active') return;
    
    const interval = setInterval(() => {
      const remaining = Math.max(0, Math.floor((qrData.expiresAt - Date.now()) / 1000));
      setTimeLeft(remaining);
      
      if (remaining <= 0) {
        clearInterval(interval);
      }
    }, 1000);
    
    return () => clearInterval(interval);
  }, [qrData, status]);
  
  const handleRefresh = () => {
    if (qrType) {
      generateQR(qrType);
    }
  };
  
  const getStatusDisplay = () => {
    switch (status) {
      case 'loading':
        return {
          icon: <Loader2 className="w-12 h-12 text-[#002f4f] animate-spin" />,
          title: 'Generating QR Code...',
          message: 'Please wait while we create your secure code',
        };
      case 'active':
        return {
          icon: <CheckCircle className="w-12 h-12 text-green-500" />,
          title: 'Ready to Scan',
          message: `This QR code expires in ${timeLeft} second${timeLeft !== 1 ? 's' : ''}`,
        };
      case 'used':
        return {
          icon: <AlertCircle className="w-12 h-12 text-amber-500" />,
          title: 'QR Code Already Used',
          message: 'This code has been scanned. Please generate a new one.',
        };
      case 'expired':
        return {
          icon: <AlertCircle className="w-12 h-12 text-red-500" />,
          title: 'QR Code Expired',
          message: 'The QR code has expired. Please refresh to get a new one.',
        };
      case 'error':
        return {
          icon: <AlertCircle className="w-12 h-12 text-red-500" />,
          title: 'Generation Failed',
          message: errorMessage || 'Something went wrong. Please try again.',
        };
      default:
        return null;
    }
  };
  
  const statusDisplay = getStatusDisplay();
  const isActive = status === 'active';
  const qrValue = qrData ? JSON.stringify({ token: qrData.token, type: qrData.type, timestamp: qrData.createdAt }) : '';
  
  if (!isOpen) return null;
  
  return (
    <div 
      className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-all duration-300"
      onClick={onClose}
      role="dialog"
      aria-modal="true"
      aria-labelledby="qr-modal-title"
    >
      <div 
        className="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto overflow-hidden transform transition-all"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Modal Header */}
        <div className="flex items-center justify-between p-5 border-b border-gray-100 bg-gradient-to-r from-[#f8fafc] to-white">
          <div className="flex items-center gap-2">
            <QrCode className="w-5 h-5 text-[#002f4f]" />
            <h2 id="qr-modal-title" className="text-xl font-semibold text-[#002f4f]">
              {qrType === 'clock-in' ? 'Clock In' : 'Clock Out'} QR Code
            </h2>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-full hover:bg-gray-100 transition-colors"
            aria-label="Close modal"
          >
            <X className="w-5 h-5 text-gray-500" />
          </button>
        </div>
        
        {/* Modal Body */}
        <div className="p-6 flex flex-col items-center text-center">
          {/* Status Icon & Message */}
          <div className="mb-4">
            {statusDisplay?.icon}
          </div>
          <h3 className="text-lg font-medium text-gray-800 mb-1">
            {statusDisplay?.title}
          </h3>
          <p className="text-sm text-gray-500 mb-6">
            {statusDisplay?.message}
          </p>
          
          {/* QR Code Display */}
          <div className="bg-white p-4 rounded-xl shadow-md border border-gray-100 mb-6">
            {isActive && qrData ? (
              <div className="relative">
                <QRCodeCanvas
                  value={qrValue}
                  size={200}
                  level="H"
                  includeMargin={true}
                  className="rounded-lg"
                />
                <div className="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-0.5 rounded-full shadow-md">
                  Active
                </div>
              </div>
            ) : (
              <div className="w-[200px] h-[200px] bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200">
                {status !== 'loading' && (
                  <span className="text-gray-400 text-sm">No active QR</span>
                )}
              </div>
            )}
          </div>
          
          {/* Refresh Button */}
          <button
            onClick={handleRefresh}
            disabled={status === 'loading'}
            className={`inline-flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all ${
              status === 'loading'
                ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                : 'bg-[#002f4f] text-white hover:bg-[#003f5f] shadow-sm hover:shadow'
            }`}
            aria-label="Generate new QR code"
          >
            <RefreshCw className={`w-4 h-4 ${status === 'loading' ? 'animate-spin' : ''}`} />
            Generate New QR
          </button>
          
          <p className="text-xs text-gray-400 mt-4">
            ⚡ Single-use only • Expires after 60 seconds
          </p>
        </div>
      </div>
    </div>
  );
};

export default QRModal;