import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('../../src/config/supabase.ts', () => ({
  supabase: {
    from: vi.fn(),
  },
}));

import { fetchRecentActivity, fetchCurrentlyOnsite } from '../../src/models/adminDashboardDb.js';
import { supabase } from '../../src/config/supabase.js';

describe('AdminDashboard DB', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('fetchRecentActivity', () => {
    it('should call supabase with correct table and ordering', async () => {
      const mockChain = {
        select: vi.fn().mockReturnThis(),
        order: vi.fn().mockReturnThis(),
        range: vi.fn().mockResolvedValue({ data: [], error: null }),
      };
      vi.mocked(supabase.from).mockReturnValue(mockChain as any);

      await fetchRecentActivity(1, 10);

      expect(supabase.from).toHaveBeenCalledWith('attendance_logs');
      expect(mockChain.order).toHaveBeenCalledWith('event_time', { ascending: false });
      expect(mockChain.range).toHaveBeenCalledWith(0, 9);
    });

    it('should calculate range correctly for page 2', async () => {
      const mockChain = {
        select: vi.fn().mockReturnThis(),
        order: vi.fn().mockReturnThis(),
        range: vi.fn().mockResolvedValue({ data: [], error: null }),
      };
      vi.mocked(supabase.from).mockReturnValue(mockChain as any);

      await fetchRecentActivity(2, 10);

      expect(mockChain.range).toHaveBeenCalledWith(10, 19);
    });

    it('should return empty array when no data', async () => {
      const mockChain = {
        select: vi.fn().mockReturnThis(),
        order: vi.fn().mockReturnThis(),
        range: vi.fn().mockResolvedValue({ data: null, error: null }),
      };
      vi.mocked(supabase.from).mockReturnValue(mockChain as any);

      const result = await fetchRecentActivity(1, 10);

      expect(result).toEqual([]);
    });

    it('should throw when supabase returns an error', async () => {
      const mockChain = {
        select: vi.fn().mockReturnThis(),
        order: vi.fn().mockReturnThis(),
        range: vi.fn().mockResolvedValue({ data: null, error: new Error('DB error') }),
      };
      vi.mocked(supabase.from).mockReturnValue(mockChain as any);

      await expect(fetchRecentActivity(1, 10)).rejects.toThrow('DB error');
    });
  });

  describe('fetchCurrentlyOnsite', () => {
    it('should call supabase with correct table and ordering', async () => {
      const mockChain = {
        select: vi.fn().mockReturnThis(),
        order: vi.fn().mockResolvedValue({ data: [], error: null }),
      };
      vi.mocked(supabase.from).mockReturnValue(mockChain as any);

      await fetchCurrentlyOnsite();

      expect(supabase.from).toHaveBeenCalledWith('attendance_logs');
      expect(mockChain.order).toHaveBeenCalledWith('event_time', { ascending: false });
    });

    it('should return empty array when no data', async () => {
      const mockChain = {
        select: vi.fn().mockReturnThis(),
        order: vi.fn().mockResolvedValue({ data: null, error: null }),
      };
      vi.mocked(supabase.from).mockReturnValue(mockChain as any);

      const result = await fetchCurrentlyOnsite();

      expect(result).toEqual([]);
    });

    it('should throw when supabase returns an error', async () => {
      const mockChain = {
        select: vi.fn().mockReturnThis(),
        order: vi.fn().mockResolvedValue({ data: null, error: new Error('DB error') }),
      };
      vi.mocked(supabase.from).mockReturnValue(mockChain as any);

      await expect(fetchCurrentlyOnsite()).rejects.toThrow('DB error');
    });
  });
});