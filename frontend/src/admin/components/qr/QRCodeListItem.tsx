import React, { useState } from 'react';
import { LogIn, LogOut } from 'lucide-react';
import type { QRType } from '../hooks/useQRCode';

export interface QRCodeItem {
  id: string;
  name: string;
  type: QRType;
  location?: string;
  createdAt: Date;
  expiresAfter?: number;
}

interface QRCodeListItemProps {
  item: QRCodeItem;
  onRevoke?: (id: string) => void;
  onDownload?: (id: string) => void;
  onDuplicate?: (id: string) => void;
}

const QRCodeListItem: React.FC<QRCodeListItemProps> = ({ item, onRevoke, onDownload, onDuplicate }) => {
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  
  return (
    <div className="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all">
      <div className="flex items-center gap-4">
        <div className={`p-2 rounded-lg ${item.type === 'clock-in' ? 'bg-green-50' : 'bg-amber-50'}`}>
          {item.type === 'clock-in' ? (
            <LogIn className="w-5 h-5 text-green-600" />
          ) : (
            <LogOut className="w-5 h-5 text-amber-600" />
          )}
        </div>
        <div>
          <p className="font-medium text-gray-800">{item.name}</p>
          <p className="text-xs text-gray-400">
            Created {item.createdAt.toLocaleDateString()} at {item.createdAt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
            {item.expiresAfter && ` • Expires after ${item.expiresAfter}h`}
          </p>
          {item.location && <p className="text-xs text-gray-500 mt-0.5">📍 {item.location}</p>}
        </div>
      </div>
      <div className="flex items-center gap-2">
        <button 
          onClick={() => onDownload?.(item.id)}
          className="p-2 text-gray-400 hover:text-gray-600 transition-colors"
          aria-label="Download PNG"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
        </button>
        <button 
          onClick={() => onDuplicate?.(item.id)}
          className="p-2 text-gray-400 hover:text-gray-600 transition-colors"
          aria-label="Duplicate QR"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
        </button>
        <div className="relative">
          <button 
            onClick={() => setShowDeleteConfirm(true)}
            className="p-2 text-gray-400 hover:text-red-500 transition-colors"
            aria-label="Revoke QR"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
          {showDeleteConfirm && (
            <div className="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 p-2 z-10">
              <p className="text-xs text-gray-600 mb-2">Revoke this QR code?</p>
              <div className="flex gap-2">
                <button onClick={() => onRevoke?.(item.id)} className="text-xs px-2 py-1 bg-red-50 text-red-600 rounded">
                  Revoke
                </button>
                <button onClick={() => setShowDeleteConfirm(false)} className="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded">
                  Cancel
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default QRCodeListItem;