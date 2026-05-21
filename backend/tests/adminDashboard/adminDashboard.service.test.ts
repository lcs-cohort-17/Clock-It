import { describe, it, expect } from 'vitest';
import { formatRecentActivity, formatOnsite } from '../../src/services/adminDashboard.service.js';

describe('AdminDashboard Service', () => {

  describe('formatRecentActivity', () => {
    it('should format raw records into correct shape', () => {
      const input = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in' as const,
          sync_status: 'synced',
          device_info: 'mobile',
          profiles: { first_name: 'John', last_name: 'Doe' },
        },
      ];

      const result = formatRecentActivity(input);

      expect(result[0]).toEqual({
        profile_id: 'some-uuid-123',
        event_time: '2025-05-19T10:00:00Z',
        event_type: 'clock-in',
        sync_status: 'synced',
        device_info: 'mobile',
        staff: 'John Doe',
      });
    });

    it('should combine first_name and last_name into staff field', () => {
      const input = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in' as const,
          sync_status: 'synced',
          device_info: 'mobile',
          profiles: { first_name: 'Jane', last_name: 'Smith' },
        },
      ];

      const result = formatRecentActivity(input);

      expect(result[0].staff).toBe('Jane Smith');
    });

    it('should handle multiple records', () => {
      const input = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in' as const,
          sync_status: 'synced',
          device_info: 'mobile',
          profiles: { first_name: 'John', last_name: 'Doe' },
        },
        {
          profile_id: 'some-uuid-456',
          event_time: '2025-05-19T09:00:00Z',
          event_type: 'clock-out' as const,
          sync_status: 'synced',
          device_info: 'desktop',
          profiles: { first_name: 'Jane', last_name: 'Smith' },
        },
      ];

      const result = formatRecentActivity(input);

      expect(result).toHaveLength(2);
      expect(result[0].staff).toBe('John Doe');
      expect(result[1].staff).toBe('Jane Smith');
    });

    it('should handle empty array', () => {
      const result = formatRecentActivity([]);

      expect(result).toHaveLength(0);
      expect(result).toEqual([]);
    });
  });

  describe('formatOnsite', () => {
    it('should format raw onsite records into correct shape', () => {
      const input = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in' as const,
          location: 'Office',
          profiles: { first_name: 'John', last_name: 'Doe' },
        },
      ];

      const result = formatOnsite(input);

      expect(result[0]).toEqual({
        profile_id: 'some-uuid-123',
        event_time: '2025-05-19T10:00:00Z',
        location: 'Office',
        staff: 'John Doe',
      });
    });

    it('should combine first_name and last_name into staff field', () => {
      const input = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in' as const,
          location: 'Office',
          profiles: { first_name: 'Jane', last_name: 'Smith' },
        },
      ];

      const result = formatOnsite(input);

      expect(result[0].staff).toBe('Jane Smith');
    });

    it('should handle multiple onsite staff', () => {
      const input = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in' as const,
          location: 'Office',
          profiles: { first_name: 'John', last_name: 'Doe' },
        },
        {
          profile_id: 'some-uuid-456',
          event_time: '2025-05-19T09:00:00Z',
          event_type: 'clock-in' as const,
          location: 'Warehouse',
          profiles: { first_name: 'Jane', last_name: 'Smith' },
        },
      ];

      const result = formatOnsite(input);

      expect(result).toHaveLength(2);
      expect(result[0].staff).toBe('John Doe');
      expect(result[1].staff).toBe('Jane Smith');
    });

    it('should handle empty array', () => {
      const result = formatOnsite([]);

      expect(result).toHaveLength(0);
      expect(result).toEqual([]);
    });
  });
});