//tests for adminDashboard.controller.ts

import { describe, it, expect, vi, beforeEach } from 'vitest';

// Mock Supabase before importing anything that uses it
vi.mock('../../src/config/supabase.ts', () => ({
  supabase: {
    from: vi.fn(),
  },
}));

vi.mock('../../src/models/adminDashboardDb.ts');
vi.mock('../../src/services/adminDashboard.service.ts', () => ({
  formatRecentActivity: vi.fn((data) => data),
  formatOnsite: vi.fn((data) => data),
}));

import { getRecentActivityController, getCurrentlyOnsiteController } from '../../src/controllers/adminDashboardCon.js';
import * as adminDashboardDb from '../../src/models/adminDashboardDb.js';
import type { Request, Response } from 'express';

describe('AdminDashboard Controller', () => {
  let req: Partial<Request>;
  let res: Partial<Response>;

  beforeEach(() => {
    vi.clearAllMocks();
    req = {
      query: {},
    };
    res = {
      status: vi.fn().mockReturnThis(),
      json: vi.fn().mockReturnThis(),
    };
  });

  describe('getRecentActivityController', () => {
    it('should parse page and limit query params correctly', async () => {
      req.query = { page: '2', limit: '5' };

      const mockData = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in',
          sync_status: 'synced',
          device_info: 'mobile',
          profiles: [{ first_name: 'John', last_name: 'Doe' }],
        },
      ];

      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue(mockData);

      await getRecentActivityController(req as Request, res as Response);

      expect(adminDashboardDb.fetchRecentActivity

      ).toHaveBeenCalledWith(2, 5);
    });

    it('should use default page=1 if not provided', async () => {
      req.query = { limit: '10' };

      const mockData: any[] = [];
      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue(mockData);

      await getRecentActivityController(req as Request, res as Response);

      expect(adminDashboardDb.fetchRecentActivity).toHaveBeenCalledWith(1, 10);
    });

    it('should use default limit=10 if not provided', async () => {
      req.query = { page: '1' };

      const mockData: any[] = [];
      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue(mockData);

      await getRecentActivityController(req as Request, res as Response);

      expect(adminDashboardDb.fetchRecentActivity).toHaveBeenCalledWith(1, 10);
    });

    it('should return 200 status code on success', async () => {
      req.query = { page: '1', limit: '10' };

      const mockData = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in' as const,
          sync_status: 'synced',
          device_info: 'mobile',  
          profiles: [{ first_name: 'John', last_name: 'Doe' }],
        },
      ];

      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue(mockData);

      await getRecentActivityController(req as Request, res as Response);

      expect(res.status).toHaveBeenCalledWith(200);
    });

    it('should return activity data in response', async () => {
      req.query = { page: '1', limit: '10' };

      const mockData = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in' as const,
          sync_status: 'synced',
          device_info: 'mobile',
          profiles: [{ first_name: 'John', last_name: 'Doe' }],
        },
      ];
      const expectedData = [
  {
    profile_id: 'some-uuid-123',
    event_time: '2025-05-19T10:00:00Z',
    event_type: 'clock-in',
    sync_status: 'synced',
    device_info: 'mobile',
    profiles: { first_name: 'John', last_name: 'Doe' },  // object
  },
];

      vi.mocked(adminDashboardDb.fetchRecentActivity).mockResolvedValue(mockData);

      await getRecentActivityController(req as Request, res as Response);

      expect(res.json).toHaveBeenCalledWith({
        status: 'success',
        data: expectedData,
      });
    });

    it('should handle service errors with 500 status', async () => {
      req.query = { page: '1', limit: '10' };

      vi.mocked(adminDashboardDb.fetchRecentActivity).mockRejectedValue(
        new Error('Database error')
      );

      await getRecentActivityController(req as Request, res as Response);

      expect(res.status).toHaveBeenCalledWith(500);
    });

    it('should return error message on service failure', async () => {
      req.query = { page: '1', limit: '10' };

      vi.mocked(adminDashboardDb.fetchRecentActivity).mockRejectedValue(
        new Error('Database error')
      );

      await getRecentActivityController(req as Request, res as Response);

      expect(res.json).toHaveBeenCalledWith({
        status: 'error',
        message: 'Failed to fetch recent activity',
      });
    });
  });

  describe('getCurrentlyOnsiteController', () => {
    it('should return 200 status code on success', async () => {
      const mockData = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in' as const,
          location: 'Office',
          profiles: [{ first_name: 'John', last_name: 'Doe' }],
        },
      ];

      vi.mocked(adminDashboardDb.fetchCurrentlyOnsite).mockResolvedValue(mockData);

      await getCurrentlyOnsiteController(req as Request, res as Response);

      expect(res.status).toHaveBeenCalledWith(200);
    });

    it('should return onsite staff data in response', async () => {
      const mockData = [
        {
          profile_id: 'some-uuid-123',
          event_time: '2025-05-19T10:00:00Z',
          event_type: 'clock-in' as const,
          location: 'Office',
          profiles: [{ first_name: 'John', last_name: 'Doe' }],
        },
        {
          profile_id: 'some-uuid-456',
          event_time: '2025-05-19T09:00:00Z',
          event_type: 'clock-in' as const,
          location: 'Office',
          profiles: [{ first_name: 'Jane', last_name: 'Smith' }],
        },
      ];
      const expectedData = [
  {
    profile_id: 'some-uuid-123',
    event_time: '2025-05-19T10:00:00Z',
    event_type: 'clock-in',
    location: 'Office',
    profiles: { first_name: 'John', last_name: 'Doe' },  // object
  },
  {
    profile_id: 'some-uuid-456',
    event_time: '2025-05-19T09:00:00Z',
    event_type: 'clock-in',
    location: 'Office',
    profiles: { first_name: 'Jane', last_name: 'Smith' },  // object
  },
];


      vi.mocked(adminDashboardDb.fetchCurrentlyOnsite).mockResolvedValue(mockData);

      await getCurrentlyOnsiteController(req as Request, res as Response);

      expect(res.json).toHaveBeenCalledWith({
        status: 'success',
        data: expectedData,
      });
    });

    it('should handle empty onsite staff list', async () => {
      vi.mocked(adminDashboardDb.fetchCurrentlyOnsite).mockResolvedValue([]);

      await getCurrentlyOnsiteController(req as Request, res as Response);

      expect(res.status).toHaveBeenCalledWith(200);
      expect(res.json).toHaveBeenCalledWith({
        status: 'success',
        data: [],
      });
    });

    it('should handle service errors with 500 status', async () => {
      vi.mocked(adminDashboardDb.fetchCurrentlyOnsite).mockRejectedValue(
        new Error('Database error')
      );

      await getCurrentlyOnsiteController(req as Request, res as Response);

      expect(res.status).toHaveBeenCalledWith(500);
    });

    it('should return error message on service failure', async () => {
      vi.mocked(adminDashboardDb.fetchCurrentlyOnsite).mockRejectedValue(
        new Error('Database error')
      );

      await getCurrentlyOnsiteController(req as Request, res as Response);

      expect(res.json).toHaveBeenCalledWith({
        status: 'error',
        message: 'Failed to fetch currently onsite staff',
      });
    });
  });
});
