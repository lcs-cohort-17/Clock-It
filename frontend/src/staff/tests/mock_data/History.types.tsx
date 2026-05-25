// src/types/history.types.ts

export interface AttendanceRecord {
  id: string;
  employeeName: string;
  employeeId: string;
  date: string;
  checkInTime: string;
  checkOutTime: string;
  status: 'Present' | 'Absent' | 'Late' | 'Half Day' | 'Holiday';
  workingHours: number;
  department: string;
}

export type FilterStatus = AttendanceRecord['status'] | 'All';
export type SortField = keyof AttendanceRecord;
export type SortOrder = 'asc' | 'desc';