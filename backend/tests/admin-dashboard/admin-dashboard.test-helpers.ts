import { vi } from 'vitest'
import type { SupabaseClient } from '@supabase/supabase-js'

export type QueryResult = {
  count: number | null
  error: null | { message: string }
}

// ─── SUPABASE MOCK MODEL
//  * Supabase uses a fluent chain:  .from(…).select(…).is(…).eq(…) …
export function makeQueryBuilder(result: QueryResult) {
  const builder: Record<string, unknown> = {}
  const methods = ['select', 'is', 'eq', 'gte', 'lt', 'neq', 'in', 'order', 'limit']

  for (const method of methods) {
    builder[method] = () => builder
  }

  builder.then = (resolve: (v: QueryResult) => unknown) =>
    Promise.resolve(result).then(resolve)

  return builder
}

/**
 * Returns a mocked SupabaseClient whose `.from()` calls consume `results`
 * sequentially. Falls back to `{ count: 0, error: null }` once exhausted.
 */
export function makeSupabaseMock(results: QueryResult[]): SupabaseClient {
  let callIndex = 0
  return {
    from: vi.fn(() =>
      makeQueryBuilder(results[callIndex++] ?? { count: 0, error: null }),
    ),
  } as unknown as SupabaseClient
}

// ─── Shared fixtures

/** Happy-path results in the order fetchDashboardStats runs the queries. */
export const DEFAULT_COUNTS: QueryResult[] = [
  { count: 5,  error: null }, // activeSessions
  { count: 12, error: null }, // clockInsToday
  { count: 3,  error: null }, // pendingSync
  { count: 20, error: null }, // totalEventsToday
]

export const NULL_COUNTS: QueryResult[] = [
  { count: null, error: null },
  { count: null, error: null },
  { count: null, error: null },
  { count: null, error: null },
]

/**
 * Re-imports adminDashboard with a clean module registry so the module-level
 * `statsCache` object is reset between test files / describe blocks.
 */
export async function importFresh() {
  vi.resetModules()
  return import('../../adminDashboard.ts')
}