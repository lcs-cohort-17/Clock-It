// src/components/HistoryTable.tsx

import React from 'react';
import type { AttendanceRecord, SortField, SortOrder, FilterStatus } from '../tests/mock_data/History.types';


interface HistoryTableProps {
  data: AttendanceRecord[];
  onSort: (field: SortField) => void;
  sortField: SortField;
  sortOrder: SortOrder;
  filterStatus: FilterStatus;
  onFilterChange: (status: FilterStatus) => void;
  searchTerm: string;
  onSearchChange: (term: string) => void;
}

const STATUS_OPTIONS: FilterStatus[] = ['All', 'Present', 'Absent', 'Late', 'Half Day'];

const getStatusBadgeStyles = (status: string) => {
  switch (status) {
    case 'Present':
      return 'bg-emerald-100 text-emerald-800';
    case 'Absent':
      return 'bg-red-100 text-red-800';
    case 'Late':
      return 'bg-amber-100 text-amber-800';
    case 'HalfDay':
      return 'bg-indigo-100 text-indigo-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

export const HistoryTable: React.FC<HistoryTableProps> = ({
  data,
  onSort,
  sortField,
  sortOrder,
  filterStatus,
  onFilterChange,
  searchTerm,
  onSearchChange,
}) => {
  const [currentPage, setCurrentPage] = React.useState(1);
  const itemsPerPage = 10;

  // Pagination
  const totalPages = Math.ceil(data.length / itemsPerPage);
  const paginatedData = data.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
  };

  const getSortIcon = (field: SortField) => {
    if (sortField !== field) return '↕️';
    return sortOrder === 'asc' ? '↑' : '↓';
  };

  return (
    <div className="bg-white rounded-2xl shadow-sm overflow-hidden">
      {/* Filters Bar */}
      <div className="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 p-5 border-b border-gray-100">
        {/* Search Box */}
        <div className="flex-1 min-w-[250px]">
          <input
            type="text"
            placeholder="Search by name, ID, or department..."
            value={searchTerm}
            onChange={(e) => onSearchChange(e.target.value)}
            className="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 transition-all"
          />
        </div>

        {/* Filter Group */}
        <div className="flex items-center gap-3">
          <label className="text-sm font-medium text-gray-700">Status:</label>
          <select
            value={filterStatus}
            onChange={(e) => onFilterChange(e.target.value as FilterStatus)}
            className="px-4 py-2 pr-8 border border-gray-200 rounded-xl text-sm bg-gray-50 cursor-pointer focus:outline-none focus:border-blue-500"
          >
            {STATUS_OPTIONS.map(option => (
              <option key={option} value={option}>{option}</option>
            ))}
          </select>
        </div>

        {/* Records Count */}
        <div className="text-sm text-gray-500">
          Showing {paginatedData.length} of {data.length} records
        </div>
      </div>

      {/* Table */}
      <div className="overflow-x-auto">
        <table className="w-full border-collapse">
          <thead className="bg-gray-50 border-b border-gray-100">
            <tr>
              <th onClick={() => onSort('employeeName')} className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 cursor-pointer select-none hover:bg-gray-100 transition">
                Employee {getSortIcon('employeeName')}
              </th>
              <th onClick={() => onSort('date')} className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 cursor-pointer select-none hover:bg-gray-100 transition">
                Date {getSortIcon('date')}
              </th>
              <th onClick={() => onSort('checkInTime')} className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 cursor-pointer select-none hover:bg-gray-100 transition">
                Check In {getSortIcon('checkInTime')}
              </th>
              <th onClick={() => onSort('checkOutTime')} className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 cursor-pointer select-none hover:bg-gray-100 transition">
                Check Out {getSortIcon('checkOutTime')}
              </th>
              <th onClick={() => onSort('workingHours')} className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 cursor-pointer select-none hover:bg-gray-100 transition">
                Hours {getSortIcon('workingHours')}
              </th>
              <th onClick={() => onSort('status')} className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 cursor-pointer select-none hover:bg-gray-100 transition">
                Status {getSortIcon('status')}
              </th>
            </tr>
          </thead>
          <tbody>
            {paginatedData.length === 0 ? (
              <tr>
                <td colSpan={6} className="text-center py-12 text-gray-400 text-sm">
                  No records found
                </td>
              </tr>
            ) : (
              paginatedData.map((record) => (
                <tr key={record.id} className="border-b border-gray-50 hover:bg-gray-50 transition">
                  <td className="px-4 py-3">
                    <div className="font-medium text-gray-900">{record.employeeName}</div>
                    <div className="text-xs text-gray-500">{record.employeeId} • {record.department}</div>
                  </td>
                  <td className="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                    {new Date(record.date).toLocaleDateString()}
                  </td>
                  <td className="px-4 py-3 text-sm font-mono text-gray-700">
                    {record.checkInTime || '—'}
                  </td>
                  <td className="px-4 py-3 text-sm font-mono text-gray-700">
                    {record.checkOutTime || '—'}
                  </td>
                  <td className="px-4 py-3 text-sm font-medium text-gray-900">
                    {record.workingHours ? `${record.workingHours.toFixed(1)}h` : '—'}
                  </td>
                  <td className="px-4 py-3">
                    <span className={`inline-block px-3 py-1 rounded-full text-xs font-medium ${getStatusBadgeStyles(record.status)}`}>
                      {record.status}
                    </span>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      {totalPages > 1 && (
        <div className="flex justify-center items-center gap-3 py-5 px-6 border-t border-gray-100 bg-white">
          <button
            onClick={() => handlePageChange(currentPage - 1)}
            disabled={currentPage === 1}
            className="px-4 py-2 border border-gray-200 bg-white rounded-lg text-sm cursor-pointer transition hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <span className="text-sm text-gray-500">
            Page {currentPage} of {totalPages}
          </span>
          <button
            onClick={() => handlePageChange(currentPage + 1)}
            disabled={currentPage === totalPages}
            className="px-4 py-2 border border-gray-200 bg-white rounded-lg text-sm cursor-pointer transition hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
};