import { describe, it, expect, vi, beforeEach } from 'vitest';

// Mock Supabase before importing anything that uses it
vi.mock('../../src/config/supabase.ts', () => ({
  supabase: {
    from: vi.fn(),
  },
}));

vi.mock('../../src/models/adminDashboardDb.ts');

import { getRecentActivity, getCurrentlyOnsite } from '../../src/services/adminDashboard.service.js';
import * as adminDashboardDb from '../../src/models/adminDashboardDb.js';

describe('AdminDashboard Service', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('getRecentActivity', () => {
    it('should return paginated recent activity results', async () => {
      const mockData = [
        {
          profile_id: 'some-uuid-123',
          event_type: 'clock-in' as const,
          event_time: '2025-05-19T10:00:00Z',
          sync_status: true,
          device_info: 'mobile',
          profiles: [{ first_name: 'John', last_name: 'Doe' }],
        },
        {
          profile_id: 'some-uuid-456',
          event_type: 'clock-out' as const,
          event_time: '2025-05-19T09:00:00Z',
          sync_status: true,
          
        },
      ];

      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue(mockData);

      const result = await getRecentActivity(1, 10);

    //   expect(result).toHaveLength(2);
    //   expect(result[0].staffName).toBe('John Doe');
    //   expect(result[0].employeeId).toBe('EMP001');
    //   expect(result[0].action).toBe('clock-in');
    // });

    it('should respect limit parameter', async () => {
      const mockData = Array(20)
        .fill(null)
        .map((_, i) => ({
          profile_id: `${i}`,
          staff: `Staff ${i}`,
          event_type: (i % 2 === 0 ? 'clock-in' : 'clock-out') as const,
          event_time: new Date(Date.now() - i * 60000).toISOString(),
          sync_status: true,
        }));

      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue(
        mockData.slice(0, 5)
      );

      const result = await getRecentActivity(1, 5);

      expect(result).toHaveLength(5);
      expect(adminDashboardDb.fetchRecentActivity).toHaveBeenCalledWith(
        1,
        5
      );
    });

    it('should return results sorted by timestamp DESC (newest first)', async () => {
      const mockData = [
        {
          profile_id: 'some-uuid-123',
          event_type: 'clock-in' as const,
          event_time: '2025-05-19T12:00:00Z',
          sync_status: true,
          location: 'Office',
            profiles: [{ first_name: 'John', last_name: 'Doe' }],
        },
        {
          profile_id: 'some-uuid-456',
          event_type: 'clock-out' as const,
          event_time: '2025-05-19T11:00:00Z',
          sync_status: true,
          location: 'Office',
          profiles: [{ first_name: 'Jane', last_name: 'Smith' }],
        },
      ];

      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue(mockData);

      const result = await getRecentActivity(1, 10);

      expect(new Date(result[0].event_time).getTime()).toBeGreaterThan(
        new Date(result[1].event_time).getTime()
      );
    });

    it('should handle empty dataset', async () => {
      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue([]);

      const result = await getRecentActivity(1, 10);

      expect(result).toHaveLength(0);
    });

    it('should filter out records with sync=false', async () => {
      const mockData = [
        {
          profile_id: '1',
          event_type: 'clock-in' as const,
          event_time: '2025-05-19T10:00:00Z',
          sync_status: true,
        },
        {
          profile_id: '2',
          event_type: 'clock-out' as const,
          event_time: '2025-05-19T09:00:00Z',
          sync_status: false,
        },
      ];

      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue(mockData);

      const result = await getRecentActivity(1, 10);

      expect(result).toHaveLength(1);
      expect(result[0].profile_id).toBe('John Doe');
    });

    it('should return correct response structure for recent activity', async () => {
      const mockData = [
        {
          profile_id: '1',
          event_type: 'clock-in' as const,
          event_time: '2025-05-19T10:00:00Z',
          sync_status: true,
        },
      ];

      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue(mockData);

      const result = await getRecentActivity(1, 10);

      expect(result[0]).toHaveProperty('staffName');
      expect(result[0]).toHaveProperty('employeeId');
      expect(result[0]).toHaveProperty('timestamp');
      expect(result[0]).toHaveProperty('action');
    });
  });

  describe('getOnsiteStaff', () => {
    it('should return only clock-in users (currently onsite)', async () => {
      const mockData = [
        {
          profile_id: '1',
          event_type: 'clock-in' as const,
          event_time: '2025-05-19T10:00:00Z',
          sync_status: true,
        },
        {
          profile_id: '2',
          event_type: 'clock-out' as const,
          event_time: '2025-05-19T09:00:00Z',
          sync: true,
        },
      ];

      vi.mocked(adminDashboardDb.getLatestAttendancePerUser).mockResolvedValue(mockData);

      const result = await getCurrentlyOnsite();

      expect(result).toHaveLength(1);
      expect(result[0].staffName).toBe('John Doe');
      expect(result[0].action).toBe('clock-in');
    });

    it('should return only latest record per user', async () => {
      const mockData = [
        {
          id: '1',
          type: 'clock-in' as const,
          timestamp: '2025-05-19T10:00:00Z',
          sync: true,
        },
        {
          id: '3',
          staff: 'John Doe',
          employee_id: 'EMP001',
          user_id: 'user-1',
          type: 'clock-out' as const,
          timestamp: '2025-05-19T12:00:00Z',
          sync: true,
        },
      ];

      vi.mocked(adminDashboardDb.getLatestAttendancePerUser).mockResolvedValue([
        {
          id: '3',
          staff: 'John Doe',
          employee_id: 'EMP001',
          user_id: 'user-1',
          type: 'clock-out' as const,
          timestamp: '2025-05-19T12:00:00Z',
          sync: true,
        },
      ]);

      const result = await getCurrentlyOnsite();

      expect(result).toHaveLength(0);
    });

    it('should handle multiple users correctly', async () => {
      const mockData = [
        {
          id: '1',
          staff: 'John Doe',
          employee_id: 'EMP001',
          user_id: 'user-1',
          type: 'clock-in' as const,
          timestamp: '2025-05-19T10:00:00Z',
          sync: true,
        },
        {
          id: '2',
          staff: 'Jane Smith',
          employee_id: 'EMP002',
          user_id: 'user-2',
          type: 'clock-in' as const,
          timestamp: '2025-05-19T09:00:00Z',
          sync: true,
        },
        {
          id: '3',
          staff: 'Bob Johnson',
          employee_id: 'EMP003',
          user_id: 'user-3',
          type: 'clock-in' as const,
          timestamp: '2025-05-19T08:00:00Z',
          sync: true,
        },
      ];

      vi.mocked(adminDashboardDb.getLatestAttendancePerUser).mockResolvedValue(mockData);

      const result = await getOnsiteStaff();

      expect(result).toHaveLength(3);
      expect(result.map((r) => r.staffName)).toEqual(['John Doe', 'Jane Smith', 'Bob Johnson']);
    });

    it('should filter out records with sync=false', async () => {
      const mockData = [
        {
          id: '1',
          staff: 'John Doe',
          employee_id: 'EMP001',
          user_id: 'user-1',
          type: 'clock-in' as const,
          timestamp: '2025-05-19T10:00:00Z',
          sync: true,
        },
        {
          id: '2',
          staff: 'Jane Smith',
          employee_id: 'EMP002',
          user_id: 'user-2',
          type: 'clock-in' as const,
          timestamp: '2025-05-19T09:00:00Z',
          sync: false,
        },
      ];

      vi.mocked(adminDashboardDb.getLatestAttendancePerUser).mockResolvedValue(mockData);

      const result = await getOnsiteStaff();

      expect(result).toHaveLength(1);
      expect(result[0].staffName).toBe('John Doe');
    });

    it('should handle empty dataset', async () => {
      vi.mocked(adminDashboardDb.getLatestAttendancePerUser).mockResolvedValue([]);

      const result = await getOnsiteStaff();

      expect(result).toHaveLength(0);
    });

    it('should return correct response structure for onsite staff', async () => {
      const mockData = [
        {
          id: '1',
          staff: 'John Doe',
          employee_id: 'EMP001',
          user_id: 'user-1',
          type: 'clock-in' as const,
          timestamp: '2025-05-19T10:00:00Z',
          sync: true,
        },
      ];

      vi.mocked(adminDashboardDb.getLatestAttendancePerUser).mockResolvedValue(mockData);

      const result = await getOnsiteStaff();

      expect(result[0]).toHaveProperty('staffName');
      expect(result[0]).toHaveProperty('employeeId');
      expect(result[0]).toHaveProperty('timestamp');
      expect(result[0]).toHaveProperty('action');
    });
  });
});
