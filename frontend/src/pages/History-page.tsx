// src/pages/History-page.tsx

import React, { useState, useMemo } from 'react';
import { HistoryTable } from '../components/HistoryTable';
import { mockAttendanceData } from '../tests/mock_data/Historypage-mock-data';
import type { SortField, SortOrder, FilterStatus } from '../tests/mock_data/History.types';

export const HistoryPage: React.FC = () => {
  const [sortField, setSortField] = useState<SortField>('date');
  const [sortOrder, setSortOrder] = useState<SortOrder>('desc');
  const [filterStatus, setFilterStatus] = useState<FilterStatus>('All');
  const [searchTerm, setSearchTerm] = useState('');

  // Filter and sort data
  const processedData = useMemo(() => {
    let filtered = [...mockAttendanceData];

    // Apply status filter
    if (filterStatus !== 'All') {
      filtered = filtered.filter(record => record.status === filterStatus);
    }

    // Apply search filter
    if (searchTerm.trim()) {
      const term = searchTerm.toLowerCase();
      filtered = filtered.filter(
        record =>
          record.employeeName.toLowerCase().includes(term) ||
          record.employeeId.toLowerCase().includes(term) ||
          record.department.toLowerCase().includes(term)
      );
    }

    // Apply sorting
    filtered.sort((a, b) => {
      let aVal: any = a[sortField];
      let bVal: any = b[sortField];

      if (sortField === 'date') {
        aVal = new Date(aVal).getTime();
        bVal = new Date(bVal).getTime();
      }

      if (sortField === 'workingHours') {
        aVal = aVal || 0;
        bVal = bVal || 0;
      }

      if (aVal < bVal) return sortOrder === 'asc' ? -1 : 1;
      if (aVal > bVal) return sortOrder === 'asc' ? 1 : -1;
      return 0;
    });

    return filtered;
  }, [filterStatus, searchTerm, sortField, sortOrder]);

  const handleSort = (field: SortField) => {
    if (sortField === field) {
      setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
    } else {
      setSortField(field);
      setSortOrder('asc');
    }
  };

  // Calculate summary statistics
  const summary = useMemo(() => {
    const total = processedData.length;
    const present = processedData.filter(r => r.status === 'Present').length;
    const late = processedData.filter(r => r.status === 'Late').length;
    const absent = processedData.filter(r => r.status === 'Absent').length;
    const avgHours = processedData.filter(r => r.workingHours > 0).reduce((sum, r) => sum + r.workingHours, 0) / (processedData.filter(r => r.workingHours > 0).length || 1);
    
    return { total, present, late, absent, avgHours: avgHours.toFixed(1) };
  }, [processedData]);

  return (
    <section className="min-h-screen w-full bg-[#EEF3F8] p-4 md:p-6 xl:p-8">
      {/* Header Container aligned with profile header spacing */}
      <div className="mb-8">
        <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">
          Attendance
        </p>
        <h1 className="mt-2 text-3xl font-bold text-slate-800 md:text-4xl">
          Attendance History
        </h1>
        <p className="mt-2 text-sm text-slate-500">
          View and manage employee attendance records
        </p>
      </div>
      
      {/* Main content wrapper constrained to match profile spacing layout */}
      <div className="mx-auto flex max-w-5xl flex-col gap-6">
        
        {/* Stats Cards Dashboard Pane */}
        <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <h2 className="text-xl font-semibold text-[#093C5D] mb-4">
            Metrics Overview
          </h2>
          <div className="flex gap-2 md:gap-4 flex-wrap">
            <div className="bg-slate-50 rounded-2xl px-4 py-3 min-w-[70px] md:min-w-[100px] flex-1 text-center border border-slate-100">
              <span className="block text-xs font-medium text-slate-500 mb-1">Total Records</span>
              <span className="block text-xl md:text-2xl font-bold text-slate-800">{summary.total}</span>
            </div>
            <div className="bg-slate-50 rounded-2xl px-4 py-3 min-w-[70px] md:min-w-[100px] flex-1 text-center border border-slate-100">
              <span className="block text-xs font-medium text-slate-500 mb-1">Present</span>
              <span className="block text-xl md:text-2xl font-bold text-emerald-600">{summary.present}</span>
            </div>
            <div className="bg-slate-50 rounded-2xl px-4 py-3 min-w-[70px] md:min-w-[100px] flex-1 text-center border border-slate-100">
              <span className="block text-xs font-medium text-slate-500 mb-1">Late</span>
              <span className="block text-xl md:text-2xl font-bold text-amber-600">{summary.late}</span>
            </div>
            <div className="bg-slate-50 rounded-2xl px-4 py-3 min-w-[70px] md:min-w-[100px] flex-1 text-center border border-slate-100">
              <span className="block text-xs font-medium text-slate-500 mb-1">Absent</span>
              <span className="block text-xl md:text-2xl font-bold text-red-500">{summary.absent}</span>
            </div>
            <div className="bg-slate-50 rounded-2xl px-4 py-3 min-w-[70px] md:min-w-[100px] flex-1 text-center border border-slate-100">
              <span className="block text-xs font-medium text-slate-500 mb-1">Avg Hours</span>
              <span className="block text-xl md:text-2xl font-bold text-blue-500">{summary.avgHours}h</span>
            </div>
          </div>
        </div>

        {/* History Table Container */}
        <div className="bg-white rounded-3xl border border-slate-200 p-4 md:p-6 shadow-sm">
          <HistoryTable
            data={processedData}
            onSort={handleSort}
            sortField={sortField}
            sortOrder={sortOrder}
            filterStatus={filterStatus}
            onFilterChange={setFilterStatus}
            searchTerm={searchTerm}
            onSearchChange={setSearchTerm}
          />
        </div>
      </div>
    </section>
  );
};