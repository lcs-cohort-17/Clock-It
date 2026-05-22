// clearCacheController.test.ts
import { describe, it, expect, vi, beforeEach } from 'vitest';
import type { Request, Response } from 'express';

// --- mocks ---
const mockKeys = vi.fn();
const mockDel = vi.fn();

const mockEq = vi.fn();
const mockUpdate = vi.fn(() => ({
  eq: mockEq,
}));

const mockFrom = vi.fn(() => ({
  update: mockUpdate,
}));

vi.mock('node-cache', () => {
  return {
    default: vi.fn().mockImplementation(() => ({
      keys: mockKeys,
      del: mockDel,
    })),
  };
});

vi.mock('@supabase/supabase-js', () => {
  return {
    createClient: vi.fn(() => ({
      from: mockFrom,
    })),
  };
});

// IMPORTANT:
// import AFTER mocks
import { clearCacheController } from '../../src/controllers/cacheController.js';

describe('clearCacheController', () => {
  let req: Partial<Request>;
  let res: Partial<Response>;

  beforeEach(() => {
    vi.clearAllMocks();

    res = {
      status: vi.fn().mockReturnThis(),
      json: vi.fn(),
    };
  });

  it('should return 401 if no user session exists', async () => {
    req = {
      user: undefined,
    };

    await clearCacheController(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(401);
    expect(res.json).toHaveBeenCalledWith({
      error: 'Unauthorized: no user session found',
    });
  });

  it('should clear only the user cache keys and return 200', async () => {
    req = {
      user: {
        userId: 'user-123',
        email: 'user@example.com',
        role: 'user',
        employee_id: 'emp-123',
      },
    };

    mockKeys.mockReturnValue([
      'user:user-123:posts',
      'user:user-123:profile',
      'user:someone-else:data',
    ]);

    mockEq.mockResolvedValue({
      error: null,
    });

    await clearCacheController(req as Request, res as Response);

    expect(mockDel).toHaveBeenCalledWith([
      'user:user-123:posts',
      'user:user-123:profile',
    ]);

    expect(mockFrom).toHaveBeenCalledWith('cache_control');

    expect(mockUpdate).toHaveBeenCalledWith({
      cleared_at: expect.any(String),
    });

    expect(mockEq).toHaveBeenCalledWith('user_id', 'user-123');

    expect(res.status).toHaveBeenCalledWith(200);

    expect(res.json).toHaveBeenCalledWith({
      message: 'Cache cleared successfully',
    });
  });

  it('should return 500 if supabase update fails', async () => {
    req = {
      user: {
        userId: 'user-123',
        email: 'user@example.com',
        role: 'user',
        employee_id: 'emp-123',
      },
    };

    mockKeys.mockReturnValue([]);

    mockEq.mockResolvedValue({
      error: {
        message: 'Database exploded',
      },
    });

    await clearCacheController(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(500);

    expect(res.json).toHaveBeenCalledWith({
      error: 'Database exploded',
    });
  });

  it('should return 500 for unexpected errors', async () => {
    req = {
      user: {
        userId: 'user-123',
        email: 'user@example.com',
        role: 'user',
        employee_id: 'emp-123',
      },
    };

    mockKeys.mockImplementation(() => {
      throw new Error('Cache failure');
    });

    await clearCacheController(req as Request, res as Response);

    expect(res.status).toHaveBeenCalledWith(500);

    expect(res.json).toHaveBeenCalledWith({
      error: 'Cache failure',
    });
  });
});