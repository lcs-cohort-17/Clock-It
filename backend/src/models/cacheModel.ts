import { supabase } from '../config/supabase.js';
import NodeCache from 'node-cache';

const cache = new NodeCache();

/**
 * Clears cache for a specific user
 */
export const clearUserCache = async (userId: string) => {
  // 1. Remove in-memory cache (NodeCache)
  const userCacheKey = `user:${userId}`;

  const allKeys = cache.keys();
  const userKeys = allKeys.filter((key) =>
    key.startsWith(userCacheKey)
  );

  cache.del(userKeys);

  // 2. Update Supabase cache tracking table
  const { error } = await supabase
    .from('cache_control')
    .update({
      cleared_at: new Date().toISOString(),
    })
    .eq('user_id', userId);

  if (error) {
    throw new Error(error.message);
  }

  return true;
};