import { describe, it, expect, vi, beforeEach } from 'vitest'
import request from 'supertest'
import express from 'express'

// ─── MOCK CONTROLLERS ───────────────────────────────────────
vi.mock('../../src/controllers/profileController.js', () => ({
  getProfilesCon: vi.fn((req, res) => {
    res.status(200).json({ success: true, message: 'getProfilesCon hit' })
  }),

  updateProfileCon: vi.fn((req, res) => {
    res.status(200).json({ success: true, message: 'updateProfileCon hit' })
  }),

  deleteProfileCon: vi.fn((req, res) => {
    res.status(200).json({ success: true, message: 'deleteProfileCon hit' })
  })
}))

// ─── MOCK AUTH MIDDLEWARE ───────────────────────────────────
vi.mock('../../src/middleware/authMiddleware.js', () => ({
  authenticateToken: vi.fn((req, res, next) => {
    const authHeader = req.headers.authorization

    if (!authHeader) {
      return res.status(401).json({
        success: false,
        error: 'Access denied'
      })
    }

    next()
  })
}))

// ─── IMPORT AFTER MOCKS ─────────────────────────────────────
import profileRoutes from '../../src/routes/profileRoutes.js'

import {
  getProfilesCon,
  updateProfileCon,
  deleteProfileCon
} from '../../src/controllers/profileController.js'

// ─── MINI APP ───────────────────────────────────────────────
const app = express()

app.use(express.json())
app.use('/profiles', profileRoutes)

beforeEach(() => {
  vi.clearAllMocks()
})

// ─── GET ROUTE ──────────────────────────────────────────────
describe('GET /profiles', () => {

  it('should call getProfilesCon when authenticated', async () => {

    const response = await request(app)
      .get('/profiles')
      .set('Authorization', 'Bearer faketoken')

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)

    expect(getProfilesCon).toHaveBeenCalledTimes(1)
  })

  it('should return 401 when no token is provided', async () => {

    const response = await request(app)
      .get('/profiles')

    expect(response.status).toBe(401)
    expect(response.body.success).toBe(false)
    expect(response.body.error).toBe('Access denied')
  })
})

// ─── PATCH ROUTE ────────────────────────────────────────────
describe('PATCH /profiles/:employee_id', () => {

  it('should call updateProfileCon when authenticated', async () => {

    const response = await request(app)
      .patch('/profiles/S-007')
      .set('Authorization', 'Bearer faketoken')
      .send({ first_name: 'Siza' })

    expect(response.status).toBe(200)
    
    expect(response.body.success).toBe(true)

    expect(updateProfileCon).toHaveBeenCalledTimes(1)
  })

  it('should return 401 when no token is provided', async () => {

    const response = await request(app)
      .patch('/profiles/S-007')
      .send({ first_name: 'Ghost' })

    expect(response.status).toBe(401)
    expect(response.body.error).toBe('Access denied')
  })
})

// ─── DELETE ROUTE ───────────────────────────────────────────
describe('DELETE /profiles/:employee_id', () => {

  it('should call deleteProfileCon when authenticated', async () => {

    const response = await request(app)
      .delete('/profiles/S-007')
      .set('Authorization', 'Bearer faketoken')

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)

    expect(deleteProfileCon).toHaveBeenCalledTimes(1)
  })

  it('should return 401 when no token is provided', async () => {

    const response = await request(app)
      .delete('/profiles/S-007')

    expect(response.status).toBe(401)
    expect(response.body.error).toBe('Access denied')
  })
})