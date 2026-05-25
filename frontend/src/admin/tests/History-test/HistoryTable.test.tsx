import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import { HistoryTable } from '../components/HistoryTable';
import type { AttendanceRecord } from '../types/History.types';

const mockData: AttendanceRecord[] = [
  {
    id: '1',
    employeeName: 'Qaasim Davids',
    employeeId: 'EMP001',
    date: '2026-05-19',
    checkInTime: '08:00',
    checkOutTime: '17:00',
    status: 'Present',
    workingHours: 8,
    department: 'Engineering',
  },
  {
    id: '2',
    employeeName: 'Joshua Jacobs',
    employeeId: 'EMP002',
    date: '2026-05-18',
    checkInTime: '09:00',
    checkOutTime: '17:00',
    status: 'Late',
    workingHours: 7.5,
    department: 'Marketing',
  },
];

describe('HistoryTable Component', () => {
  const mockSort = vi.fn();
  const mockFilterChange = vi.fn();
  const mockSearchChange = vi.fn();

  it('renders attendance records correctly', () => {
    render(
      <HistoryTable
        data={mockData}
        onSort={mockSort}
        sortField="date"
        sortOrder="desc"
        filterStatus="All"
        onFilterChange={mockFilterChange}
        searchTerm=""
        onSearchChange={mockSearchChange}
      />
    );

    expect(screen.getByText('Qaasim Davids')).toBeInTheDocument();
    expect(screen.getByText('Joshua Jacobs')).toBeInTheDocument();
    expect(screen.getByText('Engineering')).toBeInTheDocument();
    expect(screen.getByText('Marketing')).toBeInTheDocument();
  });

  it('renders table headers correctly', () => {
    render(
      <HistoryTable
        data={mockData}
        onSort={mockSort}
        sortField="date"
        sortOrder="desc"
        filterStatus="All"
        onFilterChange={mockFilterChange}
        searchTerm=""
        onSearchChange={mockSearchChange}
      />
    );

    expect(screen.getByText(/Employee/i)).toBeInTheDocument();
    expect(screen.getByText(/Date/i)).toBeInTheDocument();
    expect(screen.getByText(/Check In/i)).toBeInTheDocument();
    expect(screen.getByText(/Check Out/i)).toBeInTheDocument();
    expect(screen.getByText(/Hours/i)).toBeInTheDocument();
    expect(screen.getByText(/Status/i)).toBeInTheDocument();
  });

  it('calls onSearchChange when typing in search input', () => {
    render(
      <HistoryTable
        data={mockData}
        onSort={mockSort}
        sortField="date"
        sortOrder="desc"
        filterStatus="All"
        onFilterChange={mockFilterChange}
        searchTerm=""
        onSearchChange={mockSearchChange}
      />
    );

    const input = screen.getByPlaceholderText(
      /Search by name, ID, or department/i
    );

    fireEvent.change(input, {
      target: { value: 'Qaasim' },
    });

    expect(mockSearchChange).toHaveBeenCalledWith('Qaasim');
  });

  it('calls onFilterChange when selecting a status', () => {
    render(
      <HistoryTable
        data={mockData}
        onSort={mockSort}
        sortField="date"
        sortOrder="desc"
        filterStatus="All"
        onFilterChange={mockFilterChange}
        searchTerm=""
        onSearchChange={mockSearchChange}
      />
    );

    const select = screen.getByRole('combobox');

    fireEvent.change(select, {
      target: { value: 'Present' },
    });

    expect(mockFilterChange).toHaveBeenCalledWith('Present');
  });

  it('calls onSort when clicking table headers', () => {
    render(
      <HistoryTable
        data={mockData}
        onSort={mockSort}
        sortField="date"
        sortOrder="desc"
        filterStatus="All"
        onFilterChange={mockFilterChange}
        searchTerm=""
        onSearchChange={mockSearchChange}
      />
    );

    fireEvent.click(screen.getByText(/Employee/i));

    expect(mockSort).toHaveBeenCalledWith('employeeName');
  });

  it('shows empty state when no records exist', () => {
    render(
      <HistoryTable
        data={[]}
        onSort={mockSort}
        sortField="date"
        sortOrder="desc"
        filterStatus="All"
        onFilterChange={mockFilterChange}
        searchTerm=""
        onSearchChange={mockSearchChange}
      />
    );

    expect(screen.getByText(/No records found/i)).toBeInTheDocument();
  });
});