import type { Request, Response } from 'express'
import type { SupabaseClient } from '@supabase/supabase-js'
import { fetchDashboardStats, type DashboardStats } from '../models/adminDashboardDb.ts'

const statsCache = {
  data: null as null | DashboardStats,
  expiresAt: 0,
}
const cacheTtlMs = 5000

export function handleGetStats(supabase: SupabaseClient) {
  return async (req: Request, res: Response) => {
    if (Date.now() < statsCache.expiresAt && statsCache.data) {
      return res.json(statsCache.data)
    }

    try {
      const responsePayload = await fetchDashboardStats(supabase)
      statsCache.data = responsePayload
      statsCache.expiresAt = Date.now() + cacheTtlMs
      return res.json(responsePayload)
    } catch (error) {
      return res.status(500).json({ error: String(error ?? 'Unable to fetch dashboard statistics') })
    }
  }
}
