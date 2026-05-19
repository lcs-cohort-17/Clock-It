import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import request from 'supertest'
import express from 'express'
import {
  makeSupabaseMock,
  DEFAULT_COUNTS,
  importFresh,
  type QueryResult,
} from './admin-dashboard.test-helpers.js'
import type { SupabaseClient } from '@supabase/supabase-js'
import { makeQueryBuilder } from './admin-dashboard.test-helpers.js'

beforeEach(() => vi.useFakeTimers())
afterEach(() => vi.useRealTimers())

// ─── Cache
describe('stats controller - cache warm', () => {
  it('does not call Supabase on a second request within the TTL', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const supabase = makeSupabaseMock([
      ...DEFAULT_COUNTS,
      // These should never be consumed while the cache is warm (valid and not expired data)
      { count: 99, error: null },
      { count: 99, error: null },
      { count: 99, error: null },
      { count: 99, error: null },
    ])

    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))

    await request(app).get('/api/admin/dashboard/stats')
    vi.advanceTimersByTime(1000) // Bypass rate limiter
    await request(app).get('/api/admin/dashboard/stats')

    expect(supabase.from).toHaveBeenCalledTimes(4) // only the first request
  })

  it('returns identical data on a cache hit', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const supabase = makeSupabaseMock(DEFAULT_COUNTS)

    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))

    const first = await request(app).get('/api/admin/dashboard/stats')
    vi.advanceTimersByTime(1000) // Bypass rate limiter
    const second = await request(app).get('/api/admin/dashboard/stats')

    expect(second.body).toEqual(first.body)
  })

  it('serves cached data up to but not including the 5 s TTL boundary', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const supabase = makeSupabaseMock(DEFAULT_COUNTS)

    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))

    await request(app).get('/api/admin/dashboard/stats')
    vi.advanceTimersByTime(4999) // 1 ms before expiry
    vi.advanceTimersByTime(1000) // Bypass rate limiter for second request
    await request(app).get('/api/admin/dashboard/stats')

    expect(supabase.from).toHaveBeenCalledTimes(8) // still cached
  })
})

// ─── Cache misses / TTL expiry
describe('stats controller - cache expiry', () => {
  it('re-fetches from Supabase after the 5 s TTL expires', async () => {
    const { buildAdminDashboardRouter } = await importFresh()

    const secondBatch: QueryResult[] = [
      { count: 50, error: null },
      { count: 60, error: null },
      { count: 70, error: null },
      { count: 80, error: null },
    ]
    const supabase = makeSupabaseMock([...DEFAULT_COUNTS, ...secondBatch])

    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))

    const first = await request(app).get('/api/admin/dashboard/stats')
    expect(first.body.currentlyOnsite.value).toBe(5)

    vi.advanceTimersByTime(5001) // past the TTL
    vi.advanceTimersByTime(1000) // Bypass rate limiter for second request

    const second = await request(app).get('/api/admin/dashboard/stats')
    expect(second.body.currentlyOnsite.value).toBe(50)
    expect(supabase.from).toHaveBeenCalledTimes(8) // 4 initial + 4 after expiry
  })

  it('reflects updated counts in the response after TTL expires', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const supabase = makeSupabaseMock([
      ...DEFAULT_COUNTS,
      { count: 100, error: null },
      { count: 200, error: null },
      { count: 300, error: null },
      { count: 400, error: null },
    ])

    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))

    await request(app).get('/api/admin/dashboard/stats')
    vi.advanceTimersByTime(5001) // past the TTL
    vi.advanceTimersByTime(1000) // Bypass rate limiter
    const res = await request(app).get('/api/admin/dashboard/stats')

    expect(res.body.currentlyOnsite.value).toBe(100)
    expect(res.body.totalClockedInToday.value).toBe(200)
    expect(res.body.pendingSync.value).toBe(300)
    expect(res.body.totalEventsToday.value).toBe(400)
  })
})

// ─── Error handling
describe('stats controller - failed responses are not cached', () => {
  it('retries Supabase on the next request after a failed response', async () => {
    const { buildAdminDashboardRouter } = await importFresh()

    let callCount = 0
    const supabase = {
      from: vi.fn(() => {
        callCount++
        // First four calls (first request) fail; subsequent calls succeed
        const failing = callCount <= 4
        return makeQueryBuilder(
          failing
            ? { count: null, error: { message: 'transient failure' } }
            : { count: 7, error: null },
        )
      }),
    } as unknown as SupabaseClient

    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))

    const first = await request(app).get('/api/admin/dashboard/stats')
    expect(first.status).toBe(500)
    
    vi.advanceTimersByTime(1000) // Bypass rate limiter

    const second = await request(app).get('/api/admin/dashboard/stats')
    expect(second.status).toBe(200)
    expect(second.body.currentlyOnsite.value).toBe(7)
  })

  it('keeps retrying Supabase on every failed request', async () => {
    const { buildAdminDashboardRouter } = await importFresh()

    const supabase = makeSupabaseMock([
      // Two full failing rounds (8 calls)
      { count: null, error: { message: 'fail' } },
      { count: null, error: { message: 'fail' } },
      { count: null, error: { message: 'fail' } },
      { count: null, error: { message: 'fail' } },
      { count: null, error: { message: 'fail' } },
      { count: null, error: { message: 'fail' } },
      { count: null, error: { message: 'fail' } },
      { count: null, error: { message: 'fail' } },
    ])

    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))

    const first = await request(app).get('/api/admin/dashboard/stats')
    expect(first.status).toBe(500)
    
    vi.advanceTimersByTime(1000) // Bypass rate limiter
    
    const second = await request(app).get('/api/admin/dashboard/stats')
    expect(second.status).toBe(500)
    expect(supabase.from).toHaveBeenCalledTimes(8) // both requests hit Supabase
  })
})