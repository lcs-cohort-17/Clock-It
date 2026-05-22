// src/pages/History-page.tsx

import React, { useState, useMemo } from 'react';
import { HistoryTable } from '../components/HistoryTable';
import { mockAttendanceData } from '../tests/mock_data/Historypage-mock-data';
import type { SortField, SortOrder, FilterStatus } from '../tests/mock_data/History.types';
//fix
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
    <div className="font-['Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif] bg-[#EEF3F8] dark:bg-[#081a2f] min-h-screen p-4 md:p-8">
      {/* Header */}
      <div className="bg-white dark:bg-[#0b2142] rounded-2xl p-6 mb-8 shadow-sm dark:border dark:border-[#163856]">
        <div className="mb-6">
          <h1 className="text-2xl font-semibold text-gray-900 dark:text-[#eff6ff] mb-1">📋 Attendance History</h1>
          <p className="text-sm text-gray-500 dark:text-[#9bb3d1]">View and manage employee attendance records</p>
        </div>
        
        {/* Stats Cards */}
        <div className="flex gap-2 md:gap-4 flex-wrap">
          <div className="bg-gray-50 dark:bg-[#0b2142] rounded-xl px-4 py-3 min-w-[70px] md:min-w-[100px] text-center border border-gray-100 dark:border-[#163856]">
            <span className="block text-xs text-gray-500 dark:text-[#9bb3d1] mb-1">Total Records</span>
            <span className="block text-xl md:text-2xl font-bold text-gray-900 dark:text-[#eff6ff]">{summary.total}</span>
          </div>
          <div className="bg-gray-50 rounded-xl px-4 py-3 min-w-[70px] md:min-w-[100px] text-center border border-gray-100">
            <span className="block text-xs text-gray-500 mb-1">Present</span>
            <span className="block text-xl md:text-2xl font-bold text-emerald-600">{summary.present}</span>
          </div>
          <div className="bg-gray-50 rounded-xl px-4 py-3 min-w-[70px] md:min-w-[100px] text-center border border-gray-100">
            <span className="block text-xs text-gray-500 mb-1">Late</span>
            <span className="block text-xl md:text-2xl font-bold text-amber-600">{summary.late}</span>
          </div>
          <div className="bg-gray-50 rounded-xl px-4 py-3 min-w-[70px] md:min-w-[100px] text-center border border-gray-100">
            <span className="block text-xs text-gray-500 mb-1">Absent</span>
            <span className="block text-xl md:text-2xl font-bold text-red-500">{summary.absent}</span>
          </div>
          <div className="bg-gray-50 rounded-xl px-4 py-3 min-w-[70px] md:min-w-[100px] text-center border border-gray-100">
            <span className="block text-xs text-gray-500 mb-1">Avg Hours</span>
            <span className="block text-xl md:text-2xl font-bold text-blue-500">{summary.avgHours}h</span>
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