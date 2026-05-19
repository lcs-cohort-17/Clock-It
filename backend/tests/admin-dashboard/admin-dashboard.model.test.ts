import { describe, it, expect, vi } from 'vitest'
import request from 'supertest'
import express from 'express'
import {
  makeSupabaseMock,
  makeQueryBuilder,
  DEFAULT_COUNTS,
  NULL_COUNTS,
  importFresh,
  type QueryResult,
} from './admin-dashboard.test-helpers.ts'
import type { SupabaseClient } from '@supabase/supabase-js'

async function buildApp(results: QueryResult[]) {
  const { buildAdminDashboardRouter } = await importFresh()
  const supabase = makeSupabaseMock(results)
  const app = express()
  app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))
  return { app, supabase }
}

// ─── Data mapping 

describe('fetchDashboardStats - data mapping', () => {
  it('maps activeSessions count to currentlyOnsite with people icon', async () => {
    const { app } = await buildApp(DEFAULT_COUNTS)
    const res = await request(app).get('/api/admin/dashboard/stats')
    expect(res.body.currentlyOnsite).toEqual({ value: 5, icon: 'people' })
  })

  it('maps clockInsToday count to totalClockedInToday with login icon', async () => {
    const { app } = await buildApp(DEFAULT_COUNTS)
    const res = await request(app).get('/api/admin/dashboard/stats')
    expect(res.body.totalClockedInToday).toEqual({ value: 12, icon: 'login' })
  })

  it('maps pendingSync count to pendingSync with sync_problem icon', async () => {
    const { app } = await buildApp(DEFAULT_COUNTS)
    const res = await request(app).get('/api/admin/dashboard/stats')
    expect(res.body.pendingSync).toEqual({ value: 3, icon: 'sync_problem' })
  })

  it('maps totalEventsToday count to totalEventsToday with event icon', async () => {
    const { app } = await buildApp(DEFAULT_COUNTS)
    const res = await request(app).get('/api/admin/dashboard/stats')
    expect(res.body.totalEventsToday).toEqual({ value: 20, icon: 'event' })
  })

  it('falls back to 0 when Supabase returns a null count for any field', async () => {
    const { app } = await buildApp(NULL_COUNTS)
    const res = await request(app).get('/api/admin/dashboard/stats')

    expect(res.body.currentlyOnsite.value).toBe(0)
    expect(res.body.totalClockedInToday.value).toBe(0)
    expect(res.body.pendingSync.value).toBe(0)
    expect(res.body.totalEventsToday.value).toBe(0)
  })
})

// ─── Supabase query targets
describe('fetchDashboardStats - Supabase query targets', () => {
  it('issues exactly four parallel queries', async () => {
    const { app, supabase } = await buildApp(DEFAULT_COUNTS)
    await request(app).get('/api/admin/dashboard/stats')
    expect(supabase.from).toHaveBeenCalledTimes(4)
  })

  it('queries sessions once and attendance_logs three times', async () => {
    const { app, supabase } = await buildApp(DEFAULT_COUNTS)
    await request(app).get('/api/admin/dashboard/stats')

    const tables = (supabase.from as ReturnType<typeof vi.fn>).mock.calls.map(
      (call) => call[0] as string,
    )
    expect(tables).toEqual([
      'sessions',
      'attendance_logs',
      'attendance_logs',
      'attendance_logs',
    ])
  })

  it('queries sessions with a null clock_out_time filter', async () => {
    const { buildAdminDashboardRouter } = await importFresh()

    const queryBuilder = makeQueryBuilder({ count: 2, error: null })
    const isSpy = vi.fn(() => queryBuilder)
    ;(queryBuilder as Record<string, unknown>).is = isSpy

    const supabase = {
      from: vi.fn((table: string) => {
        if (table === 'sessions') return queryBuilder
        return makeQueryBuilder({ count: 0, error: null })
      }),
    } as unknown as SupabaseClient

    const app = express()
    app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))
    await request(app).get('/api/admin/dashboard/stats')

    expect(isSpy).toHaveBeenCalledWith('clock_out_time', null)
  })
})

// ─── Error handling
describe('fetchDashboardStats - error handling', () => {
  it('surfaces a single Supabase error message', async () => {
    const { app } = await buildApp([
      { count: 5,    error: null },
      { count: null, error: { message: 'DB connection lost' } },
      { count: 3,    error: null },
      { count: 20,   error: null },
    ])
    const res = await request(app).get('/api/admin/dashboard/stats')
    expect(res.body.error).toContain('DB connection lost')
  })

  it('concatenates multiple error messages separated by |', async () => {
    const { app } = await buildApp([
      { count: null, error: { message: 'Error A' } },
      { count: null, error: { message: 'Error B' } },
      { count: null, error: null },
      { count: null, error: null },
    ])
    const res = await request(app).get('/api/admin/dashboard/stats')
    expect(res.body.error).toContain('Error A')
    expect(res.body.error).toContain('Error B')
    expect(res.body.error).toContain('|')
  })

  it('reports an error when all four queries fail', async () => {
    const { app } = await buildApp([
      { count: null, error: { message: 'fail 1' } },
      { count: null, error: { message: 'fail 2' } },
      { count: null, error: { message: 'fail 3' } },
      { count: null, error: { message: 'fail 4' } },
    ])
    const res = await request(app).get('/api/admin/dashboard/stats')
    expect(res.body).toHaveProperty('error')
    expect(res.body.error).toContain('fail 1')
    expect(res.body.error).toContain('fail 4')
  })
})