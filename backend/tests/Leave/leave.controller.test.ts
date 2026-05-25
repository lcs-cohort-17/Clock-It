import { describe, it, expect, vi, beforeEach } from 'vitest'
import * as attendanceDb from '../../src/models/leaveDb.js'
import { submitLeave, getCalendar, updateLeaveStatus } from '../../src/controllers/leaveController.js'

vi.mock('../../src/models/leaveDb.js')

const mockUserId = 'user-123'

const mockReqRes = (overrides = {}) => {
  const req = {
    auth: {
      userId: mockUserId,
      role: 'staff'
    },
    body: {},
    params: {},
    query: {},
    ...overrides
  } as unknown as any

  const res = {
    status: vi.fn().mockReturnThis(),
    json: vi.fn().mockReturnThis()
  } as unknown as any

  return { req, res }
}

describe('attendanceController', () => {

  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('submitLeave', () => {

    it('returns 201 when request succeeds', async () => {

      // mock findActiveProfile so user passes the active check
      vi.spyOn(attendanceDb, 'findActiveProfile')
        .mockResolvedValue({
          data: { id: mockUserId, is_active: true } as any,
          error: null
        })

      // mock insertLeaveRequest so DB is not hit
      vi.spyOn(attendanceDb, 'insertLeaveRequest')
        .mockResolvedValue({
          data: { id: 'leave-1', status: 'pending' } as any,
          error: null
        })

      const { req, res } = mockReqRes({
        body: {
          request_type: 'leave',
          reason: 'Sick'
        }
      })

      await submitLeave(req, res)

      expect(res.status).toHaveBeenCalledWith(201)
    })

    it('returns 401 when auth missing', async () => {

      const { req, res } = mockReqRes()

      req.auth = undefined as any

      await submitLeave(req, res)

      expect(res.status).toHaveBeenCalledWith(401)
    })
  })

  describe('getCalendar', () => {

    it('returns calendar data', async () => {

      vi.spyOn(attendanceDb, 'fetchCalendar')
        .mockResolvedValue({
          data: [],
          error: null
        } as any)

      const { req, res } = mockReqRes()

      await getCalendar(req, res)

      expect(res.status).toHaveBeenCalledWith(200)
    })
  })

  describe('updateLeaveStatus', () => {

    it('allows admin approval', async () => {

      // mock findLeaveById so existence check passes
      vi.spyOn(attendanceDb, 'findLeaveById')
        .mockResolvedValue({
          data: { id: 'leave-1' } as any,
          error: null
        })

      // mock updateLeaveRequestStatus so DB is not hit
      vi.spyOn(attendanceDb, 'updateLeaveRequestStatus')
        .mockResolvedValue({
          data: { id: 'leave-1', status: 'approved' } as any,
          error: null
        })

      const { req, res } = mockReqRes({
        auth: { userId: mockUserId, role: 'admin' },
        params: { id: 'leave-1' },
        body: { status: 'approved' }
      })

      await updateLeaveStatus(req, res)

      expect(res.status).toHaveBeenCalledWith(200)
    })
  })
})