import React, { useState } from 'react';
import { X } from 'lucide-react';
import type { QRType } from '../hooks/useQRCode';

interface CreateQRFormProps {
  onClose: () => void;
  onCreate: (data: { label: string; type: QRType; location: string; expiresAfter: number }) => void;
}

const CreateQRForm: React.FC<CreateQRFormProps> = ({ onClose, onCreate }) => {
  const [formData, setFormData] = useState({
    label: '',
    type: 'clock-in' as QRType,
    location: '',
    expiresAfter: 24,
  });
  
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onCreate(formData);
    onClose();
  };
  
  return (
    <div className="bg-white rounded-xl border border-gray-100 shadow-lg p-5 mb-6">
      <div className="flex justify-between items-center mb-4">
        <h3 className="font-semibold text-[#002f4f]">Create new QR code</h3>
        <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
          <X className="w-4 h-4" />
        </button>
      </div>
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Label *</label>
          <input
            type="text"
            required
            value={formData.label}
            onChange={(e) => setFormData({ ...formData, label: e.target.value })}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002f4f]/20 focus:border-[#002f4f] outline-none"
            placeholder="e.g., Main Entrance"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Type *</label>
          <select
            value={formData.type}
            onChange={(e) => setFormData({ ...formData, type: e.target.value as QRType })}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002f4f]/20 focus:border-[#002f4f] outline-none"
          >
            <option value="clock-in">Clock In</option>
            <option value="clock-out">Clock Out</option>
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Location (optional)</label>
          <input
            type="text"
            value={formData.location}
            onChange={(e) => setFormData({ ...formData, location: e.target.value })}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002f4f]/20 focus:border-[#002f4f] outline-none"
            placeholder="e.g., HQ"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Expires after (hours)</label>
          <input
            type="number"
            min="1"
            max="168"
            value={formData.expiresAfter}
            onChange={(e) => setFormData({ ...formData, expiresAfter: parseInt(e.target.value) || 24 })}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg"
          />
        </div>
        <div className="flex gap-3 pt-2">
          <button type="button" onClick={onClose} className="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit" className="flex-1 px-4 py-2 bg-[#002f4f] text-white rounded-lg hover:bg-[#003f5f]">
            Create QR
          </button>
        </div>
      </form>
    </div>
  );
};

export default CreateQRForm;