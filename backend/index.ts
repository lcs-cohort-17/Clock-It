import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import { createClient } from '@supabase/supabase-js'

dotenv.config()
const app = express()
app.use(cors())
app.use(express.json())

const port = process.env.PORT || 4321
const supabaseUrl = process.env.SUPABASE_URL
const supabaseKey = process.env.SUPABASE_KEY || process.env.SUPABASE_ANON_KEY

if (!supabaseUrl || !supabaseKey) {
  throw new Error('Missing SUPABASE_URL or SUPABASE_KEY/SUPABASE_ANON_KEY in environment')
}

const supabase = createClient(supabaseUrl, supabaseKey)

function getTodayRange() {
  const now = new Date()
  const start = new Date(now)
  start.setHours(0, 0, 0, 0)
  const end = new Date(start)
  end.setDate(end.getDate() + 1)
  return { start: start.toISOString(), end: end.toISOString() }
}

app.get('/api/admin/dashboard/stats', async (req, res) => {
  const { start, end } = getTodayRange()

  try {
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
      const message = errors.map((err) => err?.message).join(' | ')
      return res.status(500).json({ error: message })
    }

    return res.json({
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
    })
  } catch (error) {
    return res.status(500).json({ error: 'Unable to fetch dashboard statistics' })
  }
})

app.listen(port, () => {
  console.log(`http://localhost:${port}`)
})