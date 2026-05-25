import React, { useState, useCallback, useEffect, useRef } from 'react';


// Types
type QRType = 'clock-in' | 'clock-out';
type QRStatus = 'active' | 'used' | 'expired' | 'loading' | 'error';

interface QRData {
  token: string;
  type: QRType;
  expiresAt: number;
  createdAt: number;
}

// Custom hook for QR management
const useQRCode = () => {
  const [qrData, setQRData] = useState<QRData | null>(null);
  const [status, setStatus] = useState<QRStatus>('loading');
  const [errorMessage, setErrorMessage] = useState<string>('');
  
  const generateQR = useCallback(async (type: QRType) => {
    setStatus('loading');
    setErrorMessage('');
    
    try {
      // Mock API call - replace with your actual endpoint
      await new Promise(resolve => setTimeout(resolve, 800));
      
      // Simulate random failure for testing (10% chance)
      if (Math.random() < 0.1) {
        throw new Error('Network error: Failed to generate QR token');
      }
      
      const token = `${type}-${Date.now()}-${Math.random().toString(36).substring(2, 15)}`;
      const expiresIn = 60;
      const expiresAt = Date.now() + (expiresIn * 1000);
      
      setQRData({ token, type, expiresAt, createdAt: Date.now() });
      setStatus('active');
      
      const timer = setTimeout(() => {
        setStatus(prev => prev === 'active' ? 'expired' : prev);
      }, expiresIn * 1000);
      
      return () => clearTimeout(timer);
    } catch (error) {
      setStatus('error');
      setErrorMessage(error instanceof Error ? error.message : 'Failed to generate QR code');
      setQRData(null);
    }
  }, []);
  
  const reset = useCallback(() => {
    setQRData(null);
    setStatus('loading');
    setErrorMessage('');
  }, []);
  
  return { qrData, status, errorMessage, generateQR, reset };
};

// Dropdown Button Component
const QRDropdownButton: React.FC<{ onSelect: (type: QRType) => void }> = ({ onSelect }) => {
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
  
  return (
    <div className="relative" ref={dropdownRef}>
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="inline-flex items-center gap-2 px-5 py-2.5 bg-[#002f4f] text-white rounded-lg font-medium shadow-sm hover:bg-[#003f5f] transition-all"
        aria-haspopup="true"
        aria-expanded={isOpen}
      >
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Generate QR Code
        <svg className={`w-4 h-4 transition-transform ${isOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      
      {isOpen && (
        <div className="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-20">
          <button
            onClick={() => onSelect('clock-in')}
            className="w-full flex items-center gap-3 px-4 py-2.5 text-left text-gray-700 hover:bg-gray-50"
          >
            <svg className="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span>Clock In QR</span>
          </button>
          <button
            onClick={() => onSelect('clock-out')}
            className="w-full flex items-center gap-3 px-4 py-2.5 text-left text-gray-700 hover:bg-gray-50"
          >
            <svg className="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span>Clock Out QR</span>
          </button>
        </div>
      )}
    </div>
  );
};

// Simple QR Code Component (placeholder - replace with actual QR library)
const SimpleQRCode: React.FC<{ value: string; size: number }> = ({ value, size }) => {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  
  useEffect(() => {
    if (!canvasRef.current || !value) return;
    
    const canvas = canvasRef.current;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    
    // Draw a styled QR placeholder
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, size, size);
    ctx.fillStyle = '#002f4f';
    
    // Draw QR-like pattern
    const blockSize = size / 8;
    for (let i = 0; i < 8; i++) {
      for (let j = 0; j < 8; j++) {
        if ((i * j) % 3 === 0 || (i + j) % 4 === 0) {
          ctx.fillRect(i * blockSize, j * blockSize, blockSize - 1, blockSize - 1);
        }
      }
    }
    
    // Draw position markers
    const markerSize = blockSize * 2;
    ctx.fillStyle = '#002f4f';
    ctx.fillRect(0, 0, markerSize, markerSize);
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(blockSize * 0.5, blockSize * 0.5, blockSize, blockSize);
    ctx.fillStyle = '#002f4f';
    ctx.fillRect(blockSize * 0.75, blockSize * 0.75, blockSize * 0.5, blockSize * 0.5);
    
    ctx.fillStyle = '#002f4f';
    ctx.fillRect(size - markerSize, 0, markerSize, markerSize);
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(size - markerSize + blockSize * 0.5, blockSize * 0.5, blockSize, blockSize);
    
    ctx.fillStyle = '#002f4f';
    ctx.fillRect(0, size - markerSize, markerSize, markerSize);
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(blockSize * 0.5, size - markerSize + blockSize * 0.5, blockSize, blockSize);
    
    // Add small text
    ctx.fillStyle = '#666666';
    ctx.font = '10px monospace';
    ctx.fillText(value.substring(0, 12), 10, size - 5);
  }, [value, size]);
  
  return <canvas ref={canvasRef} width={size} height={size} className="rounded-lg shadow-md" />;
};

// QR Modal Component
const QRModal: React.FC<{ isOpen: boolean; onClose: () => void; qrType: QRType | null }> = ({ isOpen, onClose, qrType }) => {
  const { qrData, status, errorMessage, generateQR, reset } = useQRCode();
  const [timeLeft, setTimeLeft] = useState<number>(0);
  
  useEffect(() => {
    if (isOpen && qrType) {
      generateQR(qrType);
    }
    return () => { reset(); };
  }, [isOpen, qrType, generateQR, reset]);
  
  useEffect(() => {
    if (!qrData || status !== 'active') return;
    const interval = setInterval(() => {
      const remaining = Math.max(0, Math.floor((qrData.expiresAt - Date.now()) / 1000));
      setTimeLeft(remaining);
      if (remaining <= 0) clearInterval(interval);
    }, 1000);
    return () => clearInterval(interval);
  }, [qrData, status]);
  
  const handleRefresh = () => { if (qrType) generateQR(qrType); };
  
  const getStatusDisplay = () => {
    switch (status) {
      case 'loading':
        return { icon: '⏳', title: 'Generating QR Code...', message: 'Please wait while we create your secure code' };
      case 'active':
        return { icon: '✅', title: 'Ready to Scan', message: `This QR code expires in ${timeLeft} second${timeLeft !== 1 ? 's' : ''}` };
      case 'used':
        return { icon: '⚠️', title: 'QR Code Already Used', message: 'This code has been scanned. Please generate a new one.' };
      case 'expired':
        return { icon: '❌', title: 'QR Code Expired', message: 'The QR code has expired. Please refresh to get a new one.' };
      case 'error':
        return { icon: '⚠️', title: 'Generation Failed', message: errorMessage || 'Something went wrong. Please try again.' };
      default:
        return null;
    }
  };
  
  const statusDisplay = getStatusDisplay();
  const isActive = status === 'active';
  const qrValue = qrData ? JSON.stringify({ token: qrData.token, type: qrData.type, timestamp: qrData.createdAt }) : '';
  
  if (!isOpen) return null;
  
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" onClick={onClose}>
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto overflow-hidden" onClick={(e) => e.stopPropagation()}>
        <div className="flex items-center justify-between p-5 border-b border-gray-100">
          <div className="flex items-center gap-2">
            <svg className="w-5 h-5 text-[#002f4f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
            <h2 className="text-xl font-semibold text-[#002f4f]">{qrType === 'clock-in' ? 'Clock In' : 'Clock Out'} QR Code</h2>
          </div>
          <button onClick={onClose} className="p-1.5 rounded-full hover:bg-gray-100 text-gray-500 hover:text-gray-700">✕</button>
        </div>
        
        <div className="p-6 flex flex-col items-center text-center">
          <div className="mb-4 text-5xl">{statusDisplay?.icon}</div>
          <h3 className="text-lg font-medium text-gray-800 mb-1">{statusDisplay?.title}</h3>
          <p className="text-sm text-gray-500 mb-6">{statusDisplay?.message}</p>
          
          <div className="bg-white p-4 rounded-xl shadow-md border border-gray-100 mb-6">
            {isActive && qrData ? (
              <div className="relative">
                <SimpleQRCode value={qrValue} size={200} />
                <div className="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-0.5 rounded-full shadow-md">Active</div>
              </div>
            ) : (
              <div className="w-[200px] h-[200px] bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200">
                {status !== 'loading' && <span className="text-gray-400">No active QR</span>}
                {status === 'loading' && <div className="animate-spin text-2xl">⏳</div>}
              </div>
            )}
          </div>
          
          <button
            onClick={handleRefresh}
            disabled={status === 'loading'}
            className={`inline-flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all ${
              status === 'loading' ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-[#002f4f] text-white hover:bg-[#003f5f]'
            }`}
          >
            <svg className={`w-4 h-4 ${status === 'loading' ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Generate New QR
          </button>
          
          <p className="text-xs text-gray-400 mt-4">⚡ Single-use only • Expires after 60 seconds</p>
        </div>
      </div>
    </div>
  );
};

// Create QR Form Component
const CreateQRForm: React.FC<{ onClose: () => void; onCreate: (data: any) => void }> = ({ onClose, onCreate }) => {
  const [formData, setFormData] = useState({ label: '', type: 'clock-in' as QRType, location: '', expiresAfter: 24 });
  
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onCreate(formData);
    onClose();
  };
  
  return (
    <div className="bg-white rounded-xl border border-gray-100 shadow-lg p-5 mb-6">
      <div className="flex justify-between items-center mb-4">
        <h3 className="font-semibold text-[#002f4f]">Create new QR code</h3>
        <button onClick={onClose} className="text-gray-400 hover:text-gray-600">✕</button>
      </div>
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Label *</label>
          <input type="text" required value={formData.label} onChange={(e) => setFormData({ ...formData, label: e.target.value })}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002f4f]/20 focus:border-[#002f4f] outline-none"
            placeholder="e.g., Main Entrance" />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Type *</label>
          <select value={formData.type} onChange={(e) => setFormData({ ...formData, type: e.target.value as QRType })}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002f4f]/20 focus:border-[#002f4f] outline-none">
            <option value="clock-in">Clock In</option>
            <option value="clock-out">Clock Out</option>
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Location (optional)</label>
          <input type="text" value={formData.location} onChange={(e) => setFormData({ ...formData, location: e.target.value })}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002f4f]/20 focus:border-[#002f4f] outline-none" 
            placeholder="e.g., HQ" />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Expires after (hours)</label>
          <input type="number" min="1" max="168" value={formData.expiresAfter}
            onChange={(e) => setFormData({ ...formData, expiresAfter: parseInt(e.target.value) || 24 })}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002f4f]/20 focus:border-[#002f4f] outline-none" />
        </div>
        <div className="flex gap-3 pt-2">
          <button type="button" onClick={onClose} className="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
          <button type="submit" className="flex-1 px-4 py-2 bg-[#002f4f] text-white rounded-lg hover:bg-[#003f5f] transition-colors">Create QR</button>
        </div>
      </form>
    </div>
  );
};

// QR Code List Item Component
const QRCodeListItem: React.FC<{ item: any; onRevoke: (id: string) => void }> = ({ item, onRevoke }) => {
  const [showConfirm, setShowConfirm] = useState(false);
  
  return (
    <div className="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all">
      <div className="flex items-center gap-4">
        <div className={`p-2 rounded-lg ${item.type === 'clock-in' ? 'bg-green-50' : 'bg-amber-50'}`}>
          {item.type === 'clock-in' ? (
            <svg className="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
          ) : (
            <svg className="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
          )}
        </div>
        <div>
          <p className="font-medium text-gray-800">{item.name}</p>
          <p className="text-xs text-gray-400">
            Created {new Date(item.createdAt).toLocaleDateString()} • Expires after {item.expiresAfter}h
          </p>
          {item.location && <p className="text-xs text-gray-500 mt-0.5">📍 {item.location}</p>}
        </div>
      </div>
      <div className="relative">
        <button onClick={() => setShowConfirm(true)} className="p-2 text-gray-400 hover:text-red-500 transition-colors">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
        {showConfirm && (
          <div className="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 p-2 z-10">
            <p className="text-xs text-gray-600 mb-2">Revoke this QR code?</p>
            <div className="flex gap-2">
              <button onClick={() => onRevoke(item.id)} className="text-xs px-2 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100">Revoke</button>
              <button onClick={() => setShowConfirm(false)} className="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Cancel</button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

// Main Page Component - THIS IS WHAT YOU NEED TO EXPORT
const QRCodeGeneratorPage: React.FC = () => {
  const [modalOpen, setModalOpen] = useState(false);
  const [selectedQRType, setSelectedQRType] = useState<QRType | null>(null);
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [qrList, setQRList] = useState<any[]>([
    { id: '1', name: 'HQ Entrance', type: 'clock-in', location: 'Main Entrance', createdAt: new Date(), expiresAfter: 24 },
    { id: '2', name: 'HQ Exit', type: 'clock-out', location: 'Side Gate', createdAt: new Date(), expiresAfter: 24 },
  ]);
  
  const handleDropdownSelect = (type: QRType) => {
    setSelectedQRType(type);
    setModalOpen(true);
  };
  
  const handleCreateQR = (newQR: any) => {
    const newItem = { 
      ...newQR, 
      id: Date.now().toString(), 
      createdAt: new Date() 
    };
    setQRList([newItem, ...qrList]);
    setShowCreateForm(false);
  };
  
  const handleRevoke = (id: string) => {
    setQRList(qrList.filter(item => item.id !== id));
  };
  
  return (
    <div className="min-h-screen bg-[#f4f4f4]">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 py-6 md:py-8">
        {/* Page Header */}
        <div className="mb-6">
          <h1 className="text-2xl md:text-3xl font-bold text-[#002f4f]">QR Code Generator</h1>
          <p className="text-[#245575] mt-1">Create unique QR codes for clock-in/clock-out points.</p>
        </div>
        
        {/* Action Bar */}
        <div className="flex flex-wrap items-center justify-between gap-4 mb-8">
          <QRDropdownButton onSelect={handleDropdownSelect} />
          <button onClick={() => setShowCreateForm(!showCreateForm)}
            className="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-300 text-[#002f4f] rounded-lg font-medium shadow-sm hover:bg-gray-50 transition-all">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            New QR code
          </button>
        </div>
        
        {/* Create Form */}
        {showCreateForm && (
          <CreateQRForm onClose={() => setShowCreateForm(false)} onCreate={handleCreateQR} />
        )}
        
        {/* QR Codes List */}
        <div className="bg-white/50 rounded-xl p-1">
          <div className="flex items-center justify-between mb-4 px-2">
            <h2 className="text-lg font-semibold text-[#002f4f]">QR Codes</h2>
            <div className="flex gap-3 text-xs text-gray-500">
              <button className="hover:text-[#002f4f] transition-colors">All</button>
              <button className="hover:text-[#002f4f] transition-colors">Active</button>
              <button className="hover:text-[#002f4f] transition-colors">Expired</button>
            </div>
          </div>
          <div className="space-y-3">
            {qrList.map((item) => (
              <QRCodeListItem key={item.id} item={item} onRevoke={handleRevoke} />
            ))}
            {qrList.length === 0 && (
              <div className="text-center py-12 text-gray-400">
                <svg className="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <p>No QR codes created yet</p>
                <button onClick={() => setShowCreateForm(true)} className="text-[#002f4f] text-sm mt-2 underline hover:no-underline">
                  Create your first QR code
                </button>
              </div>
            )}
          </div>
        </div>
        
        {/* Global Quick Actions */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-8">
          <div className="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Quick Action</p>
              <p className="font-medium text-[#002f4f]">Global Clock In</p>
            </div>
            <button 
              onClick={() => handleDropdownSelect('clock-in')} 
              className="px-4 py-2 bg-green-50 text-green-700 rounded-lg text-sm font-medium hover:bg-green-100 transition-colors"
            >
              Clock In
            </button>
          </div>
          <div className="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Quick Action</p>
              <p className="font-medium text-[#002f4f]">Global Clock Out</p>
            </div>
            <button 
              onClick={() => handleDropdownSelect('clock-out')} 
              className="px-4 py-2 bg-amber-50 text-amber-700 rounded-lg text-sm font-medium hover:bg-amber-100 transition-colors"
            >
              Clock Out
            </button>
          </div>
        </div>
        
        {/* QR Modal */}
        <QRModal 
          isOpen={modalOpen} 
          onClose={() => setModalOpen(false)} 
          qrType={selectedQRType}
        />
      </div>
    </div>
  );
};

export default QRCodeGeneratorPage;