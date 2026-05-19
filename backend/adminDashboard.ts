import express from 'express'
import {rateLimit} from 'express-rate-limit'
import type { Request, Response } from 'express'
import type { SupabaseClient } from '@supabase/supabase-js'

const statsRateLimiter = rateLimit({
  windowMs: 1_000,
  max: 1,
  standardHeaders: true,
  legacyHeaders: false,
  message: { error: 'Too many requests. Max 1 request per second.' },
})

const statsCache = {
  data: null as null | Record<string, unknown>,
  expiresAt: 0,
}
const cacheTtlMs = 5000

function getTodayRange() {
  const now = new Date()
  const start = new Date(now)
  start.setHours(0, 0, 0, 0)
  const end = new Date(start)
  end.setDate(end.getDate() + 1)
  return { start: start.toISOString(), end: end.toISOString() }
}

async function fetchDashboardStats(supabase: SupabaseClient) {
  const { start, end } = getTodayRange()

  const [activeSessions, clockInsToday, pendingSync, totalEventsToday] = await Promise.all([
    supabase
      .from('sessions')
      .select('id', { count: 'exact', head: true })
      .is('clock_out_time', null),
    supabase
      .from('attendance_logs')
      .select('id', { count: 'exact', head: true })
      .eq('event_type', 'in')
      .gte('event_time', start)
      .lt('event_time', end),
    supabase
      .from('attendance_logs')
      .select('id', { count: 'exact', head: true })
      .eq('sync_status', 'pending'),
    supabase
      .from('attendance_logs')
      .select('id', { count: 'exact', head: true })
      .gte('event_time', start)
      .lt('event_time', end),
  ])

  const errors = [
    activeSessions.error,
    clockInsToday.error,
    pendingSync.error,
    totalEventsToday.error,
  ].filter(Boolean)

  if (errors.length) {
    throw new Error(errors.map((err) => err?.message).join(' | '))
  }

  return {
    currentlyOnsite: {
      value: activeSessions.count ?? 0,
      icon: 'people',
    },
    totalClockedInToday: {
      value: clockInsToday.count ?? 0,
      icon: 'login',
    },
    pendingSync: {
      value: pendingSync.count ?? 0,
      icon: 'sync_problem',
    },
    totalEventsToday: {
      value: totalEventsToday.count ?? 0,
      icon: 'event',
    },
  }
}

export function buildAdminDashboardRouter(supabase: SupabaseClient) {
  const router = express.Router()

  router.get('/stats', statsRateLimiter, async (req: Request, res: Response) => {
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
  })

  return router
}
