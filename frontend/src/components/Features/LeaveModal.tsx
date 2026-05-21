import React, { useState } from 'react';

export interface LeaveRequest {
  type: 'Leave' | 'Sick';
  start_date: string;
  end_date: string;
  reason: string;
}

interface LeaveModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (request: LeaveRequest) => void;
}

const LeaveModal: React.FC<LeaveModalProps> = ({ isOpen, onClose, onSubmit }) => {
  const [type, setType] = useState<'Leave' | 'Sick'>('Leave');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [reason, setReason] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const today = new Date().toISOString().split('T')[0];
    if (!startDate || !endDate) {
      setError('Start and end date are required.');
      return;
    }
    if (endDate < startDate) {
      setError('End date must be after or equal to start date.');
      return;
    }
    if (startDate < today) {
      setError('Start date must be today or in the future.');
      return;
    }
    if (endDate < today) {
      setError('End date must be today or in the future.');
      return;
    }
    setError('');
    const request: LeaveRequest = { type, start_date: startDate, end_date: endDate, reason };
    const savedRequests = JSON.parse(localStorage.getItem('leaveRequests') ?? '[]');
    localStorage.setItem('leaveRequests', JSON.stringify([...savedRequests, request]));
    onSubmit(request);
    onClose();
  };

  if (!isOpen) return null;

  return (
    <div className="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div className="modal-content w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
        <h2 className="text-xl font-bold text-[#093C5D]">Request Leave / Sick</h2>
        <form onSubmit={handleSubmit} className="mt-5 space-y-4">
          <label className="block text-sm font-semibold text-slate-600">
            Type
            <select className="mt-1 w-full rounded-lg border border-slate-200 p-3" value={type} onChange={e => setType(e.target.value as 'Leave' | 'Sick')}>
              <option value="Leave">Leave</option>
              <option value="Sick">Sick</option>
            </select>
          </label>
          <label className="block text-sm font-semibold text-slate-600">
            Start Date
            <input className="mt-1 w-full rounded-lg border border-slate-200 p-3" type="date" value={startDate} onChange={e => setStartDate(e.target.value)} />
          </label>
          <label className="block text-sm font-semibold text-slate-600">
            End Date
            <input className="mt-1 w-full rounded-lg border border-slate-200 p-3" type="date" value={endDate} onChange={e => setEndDate(e.target.value)} />
          </label>
          <label className="block text-sm font-semibold text-slate-600">
            Reason
            <textarea className="mt-1 min-h-24 w-full rounded-lg border border-slate-200 p-3" value={reason} onChange={e => setReason(e.target.value)} />
          </label>
          {error && <p className="error text-sm font-semibold text-red-600">{error}</p>}
          <div className="flex justify-end gap-3">
            <button className="rounded-lg px-4 py-2 text-sm font-bold text-slate-500" type="button" onClick={onClose}>Cancel</button>
            <button className="rounded-lg bg-[#9CB07A] px-4 py-2 text-sm font-bold text-[#093C5D]" type="submit">Submit</button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default LeaveModal;
