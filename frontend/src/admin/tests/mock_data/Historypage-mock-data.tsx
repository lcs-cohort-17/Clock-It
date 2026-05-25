// src/data/mock-data.tsx

// src/data/mock-data.tsx
import type { AttendanceRecord } from './History.types.tsx';

// Mock employee data
export const EMPLOYEES = [
  { name: 'Shaheed Karlie', id: 'EMP001', dept: 'Engineering' },
  { name: 'Imaan cummings', id: 'EMP002', dept: 'Sales' },
  { name: 'Will Mxbanisi', id: 'EMP003', dept: 'Marketing' },
  { name: 'Joshua Jacobs', id: 'EMP004', dept: 'Finance' },
  { name: 'Mujahid Ariefdien', id: 'EMP005', dept: 'Engineering' },
  { name: 'Nina Lewis', id: 'EMP006', dept: 'Sales' },
  { name: 'Ebrahim Easton', id: 'EMP007', dept: 'Marketing' },
  { name: 'Taaraa Haron', id: 'EMP008', dept: 'Accounting' },
  { name: 'Natheefah Rayners', id: 'EMP009', dept: 'Engineering' },
  { name: 'Keanu Visagie', id: 'EMP010', dept: 'Sales' },
  { name: 'Qaasim Davids', id: 'EMP011', dept: 'Marketing' },
  { name: 'Aiden Damon', id: 'EMP012', dept: 'Accounting' }
];

// Helper function to get random date
export const getRandomDate = (daysBack: number): string => {
  const date = new Date();
  date.setDate(date.getDate() - Math.floor(Math.random() * daysBack));
  return date.toISOString().split('T')[0];
};

// Helper to get random status
export const getRandomStatus = (): AttendanceRecord['status'] => {
  const statuses: AttendanceRecord['status'][] = ['Present', 'Present', 'Present', 'Late', 'Absent', 'Half Day', 'Holiday'];
  const randomIndex = Math.floor(Math.random() * statuses.length);
  return statuses[randomIndex];
};

// Helper to get working hours based on status
export const getWorkingHours = (status: AttendanceRecord['status']): number => {
  switch (status) {
    case 'Present': return parseFloat((8 + Math.random() * 0.5).toFixed(1));
    case 'Late': return parseFloat((7 + Math.random() * 1).toFixed(1));
    case 'Half Day': return 4;
    default: return 0;
  }
};

// Helper to get check-in time
export const getCheckInTime = (status: AttendanceRecord['status']): string => {
  if (status === 'Absent' || status === 'Holiday') return '--:--';
  const hour = status === 'Late' ? 9 + Math.floor(Math.random() * 2) : 8 + Math.floor(Math.random() * 1);
  const minute = Math.floor(Math.random() * 60);
  return `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
};

// Helper to get check-out time
export const getCheckOutTime = (status: AttendanceRecord['status'], workingHours: number): string => {
  if (status === 'Absent' || status === 'Holiday') return '--:--';
  if (status === 'Half Day') return '12:30';
  const baseHour = 17;
  const extraMinutes = Math.floor(workingHours % 1 * 60);
  return `${baseHour.toString().padStart(2, '0')}:${extraMinutes.toString().padStart(2, '0')}`;
};

// Generate mock attendance data
export const mockAttendanceData: AttendanceRecord[] = [];

for (let i = 0; i < 65; i++) {
  const employee = EMPLOYEES[i % EMPLOYEES.length];
  const status = getRandomStatus();
  const workingHours = getWorkingHours(status);
  
  mockAttendanceData.push({
    id: `ATT-${String(i + 1).padStart(4, '0')}`,
    employeeName: employee.name,
    employeeId: employee.id,
    date: getRandomDate(90),
    checkInTime: getCheckInTime(status),
    checkOutTime: getCheckOutTime(status, workingHours),
    status: status,
    workingHours: workingHours,
    department: employee.dept,
  });
}

// Sort by date (most recent first)
mockAttendanceData.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());

// FIXED: Helper function to get unique departments for filter
export const getUniqueDepartments = (): string[] => {
  const depts = new Set(mockAttendanceData.map(record => record.department));
  return ['All Departments', ...Array.from(depts)];
};

// FIXED: Helper function to get unique statuses for filter
export const getUniqueStatuses = (): Array<AttendanceRecord['status'] | 'All'> => {
  const statuses = new Set(mockAttendanceData.map(record => record.status));
  return ['All', ...Array.from(statuses)];
};