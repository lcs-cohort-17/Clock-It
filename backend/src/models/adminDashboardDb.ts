import type { SupabaseClient } from '@supabase/supabase-js'

function getTodayRange() {
  const now = new Date()
  const start = new Date(Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate()))
  const end = new Date(start)
  end.setUTCDate(end.getUTCDate() + 1)
  return { start: start.toISOString(), end: end.toISOString() }
}

export interface DashboardStats {
  currentlyOnsite: { value: number; icon: string }
  totalClockedInToday: { value: number; icon: string }
  pendingSync: { value: number; icon: string }
  totalEventsToday: { value: number; icon: string }
}

export async function fetchDashboardStats(supabase: SupabaseClient): Promise<DashboardStats> {
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