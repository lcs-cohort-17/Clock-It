import { describe, it, expect, vi } from 'vitest'
import express from 'express'
import request from 'supertest'

// mock BEFORE importing routes
// otherwise Express loads the real controllers
vi.mock('../../src/controllers/attendanceController.js', () => (
  {
  submitLeave: vi.fn((req, res) =>
    res.status(201).json({ data: {} })
  ),

  getCalendar: vi.fn((req, res) =>
    res.status(200).json({ data: {} })
  ),

  getAttendance: vi.fn((req, res) =>
    res.status(200).json({ data: [] })
  ),

  updateLeaveStatus: vi.fn((req, res) =>
    res.status(200).json({ data: {} })
  ),
}))

import attendanceRoutes from '../../src/routes/leaveRoutes.js'


// Mock auth — simulates middleware populating req.auth
const mockAuth = (
  role: 'employee' | 'admin' = 'employee'
) =>
  (req: any, _res: any, next: any) => {

    req.auth = {
      userId: 'user-uuid-1234',
      role,
      token: 'mock-token'
    }

    next()
  }


//build express app
const buildApp = (
  role: 'employee' | 'admin' = 'employee'
) => {

  const app = express()

  app.use(express.json())

  app.use(mockAuth(role))

  app.use('/api/leaves', attendanceRoutes)

  return app
}


//route integration tests
describe('attendanceRoutes', () => {
  // validator now runs BEFORE controller
  // router.post('/')
  // mounted at /api/leaves
  //
  // final route:
  // POST /api/leaves
  it('POST /api/leaves → calls submitLeave', async () => {

    const res = await request(buildApp())

      .post('/api/leaves')
      //validator requires correct and filled bodies 
      .send({

        
        request_type: 'leave',

        start_date: '2026-01-01',
        end_date: '2026-01-05',

        reason: 'Family vacation'
      })

    expect(res.status).toBe(201)
  })


  // validation failure test
  // missing required fields
  // should fail BEFORE controller executes
  it('returns 400 for invalid leave submission', async () => {

    const res = await request(buildApp())

      .post('/api/leaves')

      .send({

        // invalid body
        request_type: 'leave'
      })

    expect(res.status).toBe(400)

    expect(res.body.message)
      .toBe('Validation failed')
  })


  // router.get('/getCalendar')
  //
  // validator checks query params
  it('GET /api/leaves/getCalendar → calls getCalendar', async () => {

    const res = await request(buildApp())

      .get('/api/leaves/getCalendar')

      .query({

        // valid query params
        month: 5,
        year: 2026
      })

    expect(res.status).toBe(200)
  })


  // NEW:
  // invalid month should fail validation
  it('returns 400 for invalid calendar query', async () => {

    const res = await request(buildApp())

      .get('/api/leaves/getCalendar')

      .query({

        // invalid month
        month: 13
      })

    expect(res.status).toBe(400)

    expect(res.body.message)
      .toBe('Validation failed')
  })


  // no validator on this route
  // simple route/controller test
  it('GET /api/leaves/getAttendance → calls getAttendance', async () => {

    const res = await request(buildApp())

      .get('/api/leaves/getAttendance')

    expect(res.status).toBe(200)
  })


  // router.patch('/:id/status')
  //
  // mounted under /api/leaves
  //
  // final route:
  // PATCH /api/leaves/:id/status
  it('PATCH /api/leaves/:id/status → calls updateLeaveStatus', async () => {

    const res = await request(buildApp('admin'))

      .patch('/api/leaves/leave-uuid-5678/status')

      .send({

        // valid enum
        status: 'approved'
      })

    expect(res.status).toBe(200)
  })


  // NEW:
  // invalid enum value
  // should fail validation middleware
  it('returns 400 for invalid status update', async () => {

    const res = await request(buildApp('admin'))

      .patch('/api/leaves/leave-uuid-5678/status')

      .send({

        // invalid enum
        status: 'accepted'
      })

    expect(res.status).toBe(400)

    expect(res.body.message)
      .toBe('Validation failed')
  })


  //unknown
  it('returns 404 for unknown routes', async () => {

    const res = await request(buildApp())

      .get('/api/leaves/unknown')

    expect(res.status).toBe(404)
  })
})