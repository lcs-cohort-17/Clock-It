import React, { useState, useMemo } from 'react';
import FilterDropdown from './FilterDropdown';
import ViewToggle from './ViewToggle';
import CalendarView from './CalendarView';

// 1. Add some mock data to test if your filters actually work!
const MOCK_ATTENDANCE = [
  { id: 1, date: '2026-05-18', clockIn: '09:00 AM', clockOut: '05:00 PM', hours: '8.0 hrs', syncStatus: 'Synced' },
  { id: 2, date: '2026-05-12', clockIn: '08:45 AM', clockOut: '04:45 PM', hours: '8.0 hrs', syncStatus: 'Synced' },
  { id: 3, date: '2026-04-20', clockIn: '09:15 AM', clockOut: '06:15 PM', hours: '9.0 hrs', syncStatus: 'Synced' },
];

export default function AttendanceHistory() {
  const [view, setView] = useState('list');
  const [filter, setFilter] = useState('week'); 

  // 2. Filter logic to dynamically change what records get passed down
const filteredRecords = useMemo(() => {
  const baseDate = new Date('2026-05-18'); // Anchored to current date for testing
  
  return MOCK_ATTENDANCE.filter(record => {
    const recordDate = new Date(record.date);
    
    if (filter === 'week') {
      // Create fresh, separate instances for start and end calculation
      const startOfWeek = new Date(baseDate);
      startOfWeek.setDate(baseDate.getDate() - baseDate.getDay());
      startOfWeek.setHours(0, 0, 0, 0);

      const endOfWeek = new Date(baseDate);
      endOfWeek.setDate(baseDate.getDate() - baseDate.getDay() + 6);
      endOfWeek.setHours(23, 59, 59, 999);

      return recordDate >= startOfWeek && recordDate <= endOfWeek;
    }
    
    if (filter === 'month') {
      return recordDate.getMonth() === baseDate.getMonth() && recordDate.getFullYear() === baseDate.getFullYear();
    }
    
    return true; // 'all'
  });
}, [filter]);

  return (
    <div className="min-h-screen bg-slate-50/50 p-8 font-sans">
      <div className="mx-auto max-w-6xl">
        
        {/* Header Layout */}
        <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
          <div>
            <h1 className="text-2xl font-bold text-slate-900 tracking-tight">Attendance History</h1>
            <p className="text-sm text-slate-500 mt-1">Your past clock-in and clock-out activity.</p>
          </div>
          <div>
            <FilterDropdown currentFilter={filter} onFilterChange={setFilter} />
          </div>
        </div>

        {/* View Toggle */}
        <div className="mb-6">
          <ViewToggle currentView={view} onViewChange={setView} />
        </div>

        {/* Dynamic Display Area */}
        {view === 'list' ? (
          <div className="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            {/* Table Header */}
            <div className="grid grid-cols-5 bg-slate-50/70 border-b border-slate-200 px-6 py-4 text-xs font-semibold tracking-wider text-slate-500 uppercase">
              <div>Date</div>
              <div>Clock In</div>
              <div>Clock Out</div>
              <div>Total Hours</div>
              <div className="text-right md:text-left">Sync</div>
            </div>

            {/* Conditional Content */}
            {filteredRecords.length === 0 ? (
              <div className="flex items-center justify-center py-16 text-sm text-slate-500 font-medium">
                No records found.
              </div>
            ) : (
              <div className="divide-y divide-slate-100">
                {filteredRecords.map((record) => (
                  <div key={record.id} className="grid grid-cols-5 px-6 py-4 text-sm text-slate-700">
                    <div>{record.date}</div>
                    <div>{record.clockIn}</div>
                    <div>{record.clockOut}</div>
                    <div>{record.hours}</div>
                    <div>{record.syncStatus}</div>
                  </div>
                ))}
              </div>
            )}
          </div>
        ) : (
          /* 3. Render your actual CalendarView component with filtered data here! */
          <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm min-h-[300px]">
            <CalendarView records={filteredRecords} />
          </div>
        )}

      </div>
    </div>
  );
}