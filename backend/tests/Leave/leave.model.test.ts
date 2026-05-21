
import { describe, it, expect, vi, beforeEach } from 'vitest'

import { supabase } from '../../src/config/supabase.js'

import {
  insertLeaveRequest,
  fetchCalendar,
  updateLeaveRequestStatus
} from '../../src/models/leaveDb.js'

vi.mock('../../src/config/supabase.js', () => ({
  supabase: {
    from: vi.fn()
  }
}))


const mockUserId = 'user-uuid-1234'
const mockLeaveId = 'leave-uuid-5678'

// Import the central leave request mock data
import { centralLeaveRequest } from '../mockData/centralLeaveRequest.js';


// Model-level tests for attendance features
// These tests use mock data to simulate database interactions
// and verify that the core logic for leave/sick requests works as expected.
describe('attendanceDb', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })
 //submit leave 
  describe('insertLeaveRequest', () => {

    it('creates a leave request successfully', async () => {

      const mockRow = {
        id: mockLeaveId,
        user_id: mockUserId,
        status: 'approved',
      }

      ;(supabase.from as any).mockReturnValue({
        insert: vi.fn().mockReturnValue({
          select: vi.fn().mockReturnValue({
            single: vi.fn().mockResolvedValue({
              data: mockRow,
              error: null
            })
          })
        })
      })
       
      // ---
      // LEARNING NOTE(Yes i asked for a more detailed generated context as my notes arent always detailed ):
      // The following test passes even with empty fields because:
      // 1. This is a MODEL test, not a CONTROLLER/ROUTE test.
      // 2. The model does NOT run validation (like Zod schemas) itself.
      // 3. The test only checks if the mocked DB returns the expected result.
      //
      // In real usage, validation happens BEFORE the model is called (in the controller or middleware).
      //
      // If you want to test validation, do it in your controller/route tests, or test the schema directly.
      // ---
      // Use the central mock data for this test
      const result = await insertLeaveRequest(mockUserId, centralLeaveRequest)
      // The mock always returns mockRow, so this will pass:
      expect(result.data).toEqual(mockRow)
      expect(result.error).toBeNull()

    })
  })
//calendar data 
  describe('fetchCalendarRequests', () => {

    it('returns records for admin', async () => {

      const mockData = [
        {
          id: '1',
          status: 'approved'
        }
      ]

      ;(supabase.from as any).mockReturnValue({
        select: vi.fn().mockReturnValue({
          order: vi.fn().mockResolvedValue({
            data: mockData,
            error: null
          })
        })
      })

      const result = await fetchCalendar(
        mockUserId,
        'admin'
      )

      expect(result.data).toEqual(mockData)
    })
  })
 //check leave request status
  describe('updateLeaveRequestStatus', () => {

    it('updates request status', async () => {

      ;(supabase.from as any).mockReturnValue({
        update: vi.fn().mockReturnValue({
          eq: vi.fn().mockReturnValue({
            select: vi.fn().mockReturnValue({
              single: vi.fn().mockResolvedValue({
                data: {
                  id: mockLeaveId,
                  status: 'approved'
                },
                error: null
              })
            })
          })
        })
      })

      const result = await updateLeaveRequestStatus(
        mockLeaveId,
        'approved'
      )

      expect(result.data?.status).toBe('approved')
    })
  })
})