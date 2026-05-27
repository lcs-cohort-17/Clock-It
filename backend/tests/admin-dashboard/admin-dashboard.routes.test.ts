import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import request from 'supertest'
import express from 'express'
import {
  makeSupabaseMock,
  DEFAULT_COUNTS,
  importFresh,
} from './admin-dashboard.test-helpers.js'

beforeEach(() => vi.useFakeTimers())
afterEach(() => vi.useRealTimers())

describe('GET /stats - HTTP status codes', () => {
  it('responds with 200 on a successful fetch', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)))

    const res = await request(app).get('/api/admin/dashboard/stats')
    expect(res.status).toBe(200)
  })

  it('responds with 500 when a Supabase query returns an error', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use(
      '/api/admin/dashboard',
      buildAdminDashboardRouter(
        makeSupabaseMock([
          { count: null, error: { message: 'query failed' } },
          { count: null, error: null },
          { count: null, error: null },
          { count: null, error: null },
        ]),
      ),
    )

    const res = await request(app).get('/api/admin/dashboard/stats')
    expect(res.status).toBe(500)
  })

  it('responds with 404 for unknown sub-routes', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)))

    const res = await request(app).get('/api/admin/dashboard/unknown')
    expect(res.status).toBe(404)
    expect(res.body).toHaveProperty('error')
    expect(res.body.error).toBe('Not found')
  })

  it('responds with 404 for the router root without a sub-route', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)))

    const res = await request(app).get('/api/admin/dashboard')
    expect(res.status).toBe(404)
    expect(res.body).toHaveProperty('error')
    expect(res.body.error).toBe('Not found')
  })
})

describe('GET /stats - response body shape', () => {
  it('returns all four expected top-level stat keys', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)))

    const res = await request(app).get('/api/admin/dashboard/stats')

    expect(res.body).toHaveProperty('currentlyOnsite')
    expect(res.body).toHaveProperty('totalClockedInToday')
    expect(res.body).toHaveProperty('pendingSync')
    expect(res.body).toHaveProperty('totalEventsToday')
  })

  it('every stat object has a numeric value field', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)))

    const res = await request(app).get('/api/admin/dashboard/stats')

    for (const key of ['currentlyOnsite', 'totalClockedInToday', 'pendingSync', 'totalEventsToday']) {
      expect(res.body[key]).toHaveProperty('value')
      expect(typeof res.body[key].value).toBe('number')
    }
  })

  it('every stat object has a string icon field', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)))

    const res = await request(app).get('/api/admin/dashboard/stats')

    for (const key of ['currentlyOnsite', 'totalClockedInToday', 'pendingSync', 'totalEventsToday']) {
      expect(res.body[key]).toHaveProperty('icon')
      expect(typeof res.body[key].icon).toBe('string')
    }
  })

  it('error response contains an error string field', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use(
      '/api/admin/dashboard',
      buildAdminDashboardRouter(
        makeSupabaseMock([
          { count: null, error: { message: 'oops' } },
          { count: null, error: null },
          { count: null, error: null },
          { count: null, error: null },
        ]),
      ),
    )

    const res = await request(app).get('/api/admin/dashboard/stats')

    expect(res.body).toHaveProperty('error')
    expect(typeof res.body.error).toBe('string')
  })
})

describe('GET /stats - response headers', () => {
  it('returns JSON content-type', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)))

    const res = await request(app).get('/api/admin/dashboard/stats')

    expect(res.headers['content-type']).toMatch(/application\/json/)
  })

  it('includes RateLimit standard headers on a successful response', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)))

    const res = await request(app).get('/api/admin/dashboard/stats')

    // express-rate-limit standardHeaders: true sets RateLimit-Limit etc.
    expect(res.headers).toHaveProperty('ratelimit-limit')
    expect(res.headers).toHaveProperty('ratelimit-remaining')
  })
})


// ─── Rate limiting
describe('GET /stats - rate limiting', () => {
  it('allows the first request through', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)))

    const res = await request(app).get('/api/admin/dashboard/stats')
    expect(res.status).toBe(200)
  })

  it('blocks a second request within the same 1-second window with 429', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use(
      '/api/admin/dashboard',
      buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)),
    )

    await request(app).get('/api/admin/dashboard/stats')
    // Don't advance time - second request should be rate limited
    const blocked = await request(app).get('/api/admin/dashboard/stats')

    expect(blocked.status).toBe(429)
    expect(blocked.body).toHaveProperty('error')
    expect(blocked.body.error).toMatch(/too many requests/i)
  })

  it('rate-limit 429 response contains a human-readable error message', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use(
      '/api/admin/dashboard',
      buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)),
    )

    await request(app).get('/api/admin/dashboard/stats')
    const blocked = await request(app).get('/api/admin/dashboard/stats')

    expect(blocked.body.error).toMatch(/too many requests/i)
  })

  it('allows a request again after the 1-second window resets', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use(
      '/api/admin/dashboard',
      buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)),
    )

    await request(app).get('/api/admin/dashboard/stats')
    vi.advanceTimersByTime(1001) // Advance past the rate limit window
    const retry = await request(app).get('/api/admin/dashboard/stats')

    expect(retry.status).toBe(200)
  })

  it('ratelimit-remaining header shows 0 after the first request', async () => {
    const { buildAdminDashboardRouter } = await importFresh()
    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(makeSupabaseMock(DEFAULT_COUNTS)))

    const res = await request(app).get('/api/admin/dashboard/stats')

    // With max: 1, after one request remaining should be 0
    expect(res.headers['ratelimit-remaining']).toBe('0')
  })
})