export interface DashboardStats {
  currentlyOnsite: number;
  totalStaffToday: number;
  pendingSync: number;
  totalEvents: number;
}

export type AttendanceType = 'in' | 'out';

export interface RecentEvent {
  id: string;
  userName: string;
  type: AttendanceType;
  timestamp: string;
}

export interface OnsiteStaff {
  id: string;
  name: string;
  employeeId: string;
  clockedInAt: string;
}

export const dashboardStats: DashboardStats = {
  currentlyOnsite: 1,
  totalStaffToday: 2,
  pendingSync: 0,
  totalEvents: 12,
};

export const recentEvents: RecentEvent[] = [
  {
    id: '1',
    userName: 'Sarah Mthembu',
    type: 'in',
    timestamp: new Date().toISOString(),
  },
  {
    id: '2',
    userName: 'John Adams',
    type: 'out',
    timestamp: new Date().toISOString(),
  },
];

export const currentlyOnsiteStaff: OnsiteStaff[] = [
  {
    id: '1',
    name: 'Sarah Mthembu',
    employeeId: 'EMP001',
    clockedInAt: new Date().toISOString(),
  },
];