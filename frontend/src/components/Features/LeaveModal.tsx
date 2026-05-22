import React, { useState } from 'react';

export interface LeaveRequest {
  type: 'leave' | 'Sick';
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
  const [type, setType] = useState<'leave' | 'Sick'>('leave');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [reason, setReason] = useState('');
  const [error, setError] = useState('');

  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!startDate || !endDate) {
      setError('Start and end date are required.');
      return;
    }
    
    setError('');
    onSubmit({
      type,
      start_date: startDate,
      end_date: endDate,
      reason
    });
    
    // Clear fields on successful submit
    setStartDate('');
    setEndDate('');
    setReason('');
    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      {/* Modal Container */}
      <div className="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all duration-300 dark:border-zinc-800 dark:bg-[#0f172a]">
        
        {/* Modal Header */}
        <h2 className="text-2xl font-bold tracking-tight text-slate-900 transition-colors duration-300 dark:text-white mb-6">
          Request Leave / Sick
        </h2>

        {error && (
          <div className="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-600 dark:bg-red-950/50 dark:text-red-400">
            {error}
          </div>
        )}

        {/* Form Content */}
        <form onSubmit={handleSubmit} className="space-y-5">
          
          {/* Type Selection */}
          <div className="space-y-1.5">
            <label className="text-sm font-semibold text-slate-700 transition-colors duration-300 dark:text-slate-200">
              Type
            </label>
            <select
              value={type}
              onChange={(e) => setType(e.target.value as 'leave' | 'Sick')}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm transition-all duration-300 focus:border-[#093C5D] focus:outline-none focus:ring-2 focus:ring-[#093C5D]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:focus:border-zinc-700"
            >
              <option value="leave">Leave</option>
              <option value="Sick">Sick</option>
            </select>
          </div>

          {/* Start Date */}
          <div className="space-y-1.5">
            <label className="text-sm font-semibold text-slate-700 transition-colors duration-300 dark:text-slate-200">
              Start Date (YYYY/MM/DD)
            </label>
            <input
              type="date"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm transition-all duration-300 focus:border-[#093C5D] focus:outline-none focus:ring-2 focus:ring-[#093C5D]/20 scheme-light dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:scheme-dark dark:focus:border-zinc-700"
            />
          </div>

          {/* End Date */}
          <div className="space-y-1.5">
            <label className="text-sm font-semibold text-slate-700 transition-colors duration-300 dark:text-slate-200">
              End Date (YYYY/MM/DD)
            </label>
            <input
              type="date"
              value={endDate}
              onChange={(e) => setEndDate(e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm transition-all duration-300 focus:border-[#093C5D] focus:outline-none focus:ring-2 focus:ring-[#093C5D]/20 scheme-light dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:scheme-dark dark:focus:border-zinc-700"
            />
          </div>

          {/* Reason Description */}
          <div className="space-y-1.5">
            <label className="text-sm font-semibold text-slate-700 transition-colors duration-300 dark:text-slate-200">
              Reason
            </label>
            <textarea
              rows={4}
              value={reason}
              onChange={(e) => setReason(e.target.value)}
              placeholder="Provide context or a brief note..."
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm transition-all duration-300 placeholder:text-slate-400 focus:border-[#093C5D] focus:outline-none focus:ring-2 focus:ring-[#093C5D]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-zinc-700 resize-none"
            />
          </div>

          {/* Action Buttons */}
          <div className="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-zinc-800/60">
            <button
              type="button"
              onClick={onClose}
              className="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
            >
              Cancel
            </button>
            <button
              type="submit"
              className="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#093C5D] transition-all duration-200 hover:bg-[#072f49] shadow-sm shadow-[#093C5D]/10 dark:bg-[#b8d684] dark:text-[#0f172a] dark:hover:bg-[#a6c76f]"
            >
              Submit
            </button>
          </div>

        </form>
      </div>
    </div>
  );
};

export default LeaveModal;