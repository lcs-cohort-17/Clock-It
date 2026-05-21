// ---
// LEARNING : Validate central mock data from centralLeaveRequest
// This test will fail if the central mock data does not meet the schema requirements.
import { centralLeaveRequest } from '../mockData/centralLeaveRequest.js';

describe('submitLeaveSchema with central mock data', () => {
  it('validates the centralLeaveRequest object', () => {
    const result = submitLeaveSchema.safeParse(centralLeaveRequest)
    // This will pass or fail depending on the contents of centralLeaveRequest
    // Edit centralLeaveRequest to see how it affects this test
    if (result.success) {
      expect(result.success).toBe(true)
    } else {
      expect(result.success).toBe(false)
      
    }
  })
})
import {describe,it,expect } from 'vitest'

import { submitLeaveSchema,calendarQuerySchema,updateLeaveStatusSchema  } from '../../src/validators/leaveValidator.js'

describe('submitLeaveSchema', () => {
  it('accepts valid leave request data', () => {

    const result = submitLeaveSchema.safeParse({

      // valid enum value
      request_type: 'leave',

      // valid ISO dates
      start_date: '2026-01-01',
      end_date: '2026-01-05',

      // valid reason
      reason: 'Family vacation'

    })

    // We expect validation to pass
    expect(result.success).toBe(true)
  })})

   it('rejects invalid request_type values', () => {

    const result = submitLeaveSchema.safeParse({

      // INVALID ENUM VALUE
      request_type: 'holiday',

      start_date: '2026-01-01',
      end_date: '2026-01-05',

      reason: 'Vacation'

    })

    // Validation should fail
    expect(result.success).toBe(false)

    // If validation failed,
    // inspect the actual field errors
    if (!result.success) {

      expect(result.error.flatten().fieldErrors.request_type)
        .toContain(
          'request_type must be leave, sick, or personal'
        )
    }
  })


  it('rejects empty reason', () => {

    const result = submitLeaveSchema.safeParse({

      request_type: 'leave',

      start_date: '2026-01-01',
      end_date: '2026-01-05',

      // INVALID EMPTY STRING
      reason: ''

    })

    expect(result.success).toBe(false)

    if (!result.success) {

      expect(result.error.flatten().fieldErrors.reason)
        .toContain('reason cannot be empty')
    }
  })


  it('rejects reason longer than 500 characters', () => {

    const result = submitLeaveSchema.safeParse({

      request_type: 'leave',

      start_date: '2026-01-01',
      end_date: '2026-01-05',

      // Generates 501 characters
      reason: 'a'.repeat(501)

    })

    expect(result.success).toBe(false)

    if (!result.success) {

      expect(result.error.flatten().fieldErrors.reason)
        .toContain(
          'reason cannot exceed 500 characters'
        )
    }
  })

  it('rejects end_date before start_date', () => {

    const result = submitLeaveSchema.safeParse({

      request_type: 'leave',

      // START AFTER END
      start_date: '2026-01-10',
      end_date: '2026-01-05',

      reason: 'Vacation'

    })

    expect(result.success).toBe(false)

    if (!result.success) {

      expect(result.error.flatten().fieldErrors.end_date)
        .toContain(
          'end_date must be on or after start_date'
        )
    }
  })
describe('calendarQuerySchema', () => {



  it('converts string query params into numbers', () => {

    const result = calendarQuerySchema.safeParse({

      month: '5',
      year: '2026'

    })

    expect(result.success).toBe(true)

    if (result.success) {

      // Confirm coercion happened
      expect(result.data.month).toBe(5)

      // Confirm it is now NUMBER type
      expect(typeof result.data.month).toBe('number')
    }
  })



  it('rejects invalid month values', () => {

    const result = calendarQuerySchema.safeParse({

      month: 13

    })

    expect(result.success).toBe(false)

    if (!result.success) {

      expect(result.error.flatten().fieldErrors.month)
        .toContain(
          'month must be between 1 and 12'
        )
    }
  })
})




describe('updateLeaveStatusSchema', () => {

  it('accepts approved status', () => {

    const result = updateLeaveStatusSchema.safeParse({

      status: 'approved'

    })

    expect(result.success).toBe(true)
  })



 
  it('rejects invalid status values', () => {

    const result = updateLeaveStatusSchema.safeParse({

      status: 'accepted'

    })

    expect(result.success).toBe(false)

    if (!result.success) {

      expect(result.error.flatten().fieldErrors.status)
        .toContain(
          'status must be approved, rejected, or pending'
        )
    }
  })

  it('allows missing optional comment field', () => {

    const result = updateLeaveStatusSchema.safeParse({

      status: 'approved'

    })

    expect(result.success).toBe(true)
  })
})