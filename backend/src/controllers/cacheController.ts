import { Request, Response } from 'express';
import { clearUserCache } from '../models/cacheModel.js';

export const clearCacheController = async (
  req: Request,
  res: Response
) => {
  // 1. Auth check (controller responsibility)
  const userId = req.user?.userId;

  if (!userId) {
    return res.status(401).json({
      error: 'Unauthorized: no user session found',
    });
  }

  try {
    // 2. Call service/model
    await clearUserCache(userId);

    return res.status(200).json({
      message: 'Cache cleared successfully',
    });
  } catch (err: any) {
    return res.status(500).json({
      error: err.message ?? 'Unexpected error occurred',
    });
  }
};