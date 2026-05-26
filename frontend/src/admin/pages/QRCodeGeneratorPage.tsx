import React, { useState } from 'react';
import type { QRType } from '../components/hooks/useQRCode';
import CreateQRForm from '../components/qr/CreateQRForm';
import QRDropdownButton from '../components/qr/QRDropdownButton';
import QRCodeListItem, { type QRCodeItem } from '../components/qr/QRCodeListItem';
import QRModal from '../components/qr/QRModal';

const QRCodeGeneratorPage: React.FC = () => {
  const [modalOpen, setModalOpen] = useState(false);
  const [selectedQRType, setSelectedQRType] = useState<QRType | null>(null);
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [qrList, setQRList] = useState<QRCodeItem[]>([
    { id: '1', name: 'HQ Entrance', type: 'clock-in', location: 'Main Entrance', createdAt: new Date(), expiresAfter: 24 },
    { id: '2', name: 'HQ Exit', type: 'clock-out', location: 'Side Gate', createdAt: new Date(), expiresAfter: 24 },
  ]);
  
  const handleDropdownSelect = (type: QRType) => {
    setSelectedQRType(type);
    setModalOpen(true);
  };
  
  const handleCreateQR = (newQR: { label: string; type: QRType; location: string; expiresAfter: number }) => {
    const newItem: QRCodeItem = {
      id: Date.now().toString(),
      name: newQR.label,
      type: newQR.type,
      location: newQR.location,
      expiresAfter: newQR.expiresAfter,
      createdAt: new Date(),
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
        <div className="mb-6">
          <h1 className="text-2xl md:text-3xl font-bold text-[#002f4f]">QR Code Generator</h1>
          <p className="text-[#245575] mt-1">Create unique QR codes for clock-in/clock-out points.</p>
        </div>
        
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
        
        {showCreateForm && (
          <CreateQRForm onClose={() => setShowCreateForm(false)} onCreate={handleCreateQR} />
        )}
        
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
