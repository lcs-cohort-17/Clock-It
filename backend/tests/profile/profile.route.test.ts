import { describe, it, expect, vi, beforeEach } from 'vitest'
import request from 'supertest'
import express from 'express'

// ─── MOCK CONTROLLERS ────────────────────────────────────────
vi.mock('../../src/controllers/profileController.js', () => ({
  getProfilesCon: vi.fn((req, res) => {
    res.status(200).json({ success: true, message: 'getProfilesCon hit' })
  }),

  createProfileCon: vi.fn((req, res) => {
    res.status(201).json({ success: true, message: 'createProfileCon hit' })
  }),

  updateProfileCon: vi.fn((req, res) => {
    res.status(200).json({ success: true, message: 'updateProfileCon hit' })
  }),

  deleteProfileCon: vi.fn((req, res) => {
    res.status(200).json({ success: true, message: 'deleteProfileCon hit' })
  }),

  loginProfileCon: vi.fn((req, res) => {
    res.status(200).json({ 
      success: true, 
      token: 'mock-token',
      user: { id: 'user-123', email: 'test@test.com' }
    })
  }),
    
  resetPasswordCon: vi.fn((req, res) => res.status(200).json({ success: true, message: 'resetPasswordCon hit' })),
  updatePasswordCon: vi.fn((req, res) => res.status(200).json({ success: true, message: 'updatePasswordCon hit' }))  

  // ZAHRAA'S MOCK
  getProfileByIdCon: vi.fn((req, res) => {
    const { employee_id } = req.params  
    res.status(200).json({
      success: true,
      data: {
        id: 'user-123',
        first_name: 'Sarah',
        last_name: 'Johnson',
        employee_id: employee_id,
        email: 'sarah@company.com',
        role: 'staff',
        is_active: true
      }
    })
  })
}))

vi.mock('../../src/middleware/authMiddleware.js', () => ({
  authenticateToken: vi.fn((req, res, next) => next())
}))

import profileRoutes from '../../src/routes/profileRoutes.js'

import {
  getProfilesCon,
  updateProfileCon,
  deleteProfileCon,
  getProfileByIdCon
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

describe('Profile Routes', () => {

  it('POST /profiles route exists — create profile', async () => {
    const response = await request(app)
      .post('/profiles')
      .send({
        first_name: 'Joshua',
        last_name: 'Jacobs',
        employee_id: 'S-005',
        role: 'staff',
        email: 'jodam@gmail.com'
      })

    expect(response.status).not.toBe(404)
    expect(response.status).toBe(201)
  })

  it('POST /profiles/login route exists', async () => {
    const response = await request(app)
      .post('/profiles/login')
      .send({ email: 'jodam@gmail.com', password: 'Xk9mP2qR' })

    expect(response.status).not.toBe(404)
  })

  it('GET /profiles route exists', async () => {
    const response = await request(app).get('/profiles')
    expect(response.status).not.toBe(404)
  })

  it('PATCH /profiles/:employee_id route exists — update profile', async () => {
    const response = await request(app)
      .patch('/profiles/S-005')
      .send({ first_name: 'Updated' })

    expect(response.status).not.toBe(404)
  })

  it('DELETE /profiles/:employee_id route exists — soft delete', async () => {
    const response = await request(app).delete('/profiles/S-005')
    expect(response.status).not.toBe(404)
  })

  it('PATCH /profiles/:employee_id/reset-password route exists', async () => {
    const response = await request(app).patch('/profiles/S-005/reset-password')
    expect(response.status).not.toBe(404)
  })

  it('PATCH /profiles/:employee_id/update-password route exists', async () => {
    const response = await request(app)
      .patch('/profiles/S-005/update-password')
      .send({ oldPassword: 'IUsW0l4r', newPassword: 'newpassword' })

    expect(response.status).not.toBe(404)
  })
})

//ZAHRAA'S TESTS 
describe('GET /profiles/:employee_id', () => {

  it('should call getProfileByIdCon when authenticated', async () => {
    const response = await request(app)
      .get('/profiles/S-006')
      .set('Authorization', 'Bearer faketoken')

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)
    expect(response.body.data).toHaveProperty('first_name')
    expect(response.body.data.employee_id).toBe('S-006')
    expect(getProfileByIdCon).toHaveBeenCalledTimes(1)
  })

  it('should return 401 when no token is provided', async () => {
    const response = await request(app)
      .get('/profiles/S-006')

    expect(response.status).toBe(401)
    expect(response.body.success).toBe(false)
    expect(response.body.error).toBe('Access denied')
  })

  it('should work with different employee_id values', async () => {
    const testIds = ['A-001', 'S-999', 'EMP-123']
    
    for (const id of testIds) {
      const response = await request(app)
        .get(`/profiles/${id}`)
        .set('Authorization', 'Bearer faketoken')
      
      expect(response.status).toBe(200)
      expect(response.body.data.employee_id).toBe(id)
    }
    expect(getProfileByIdCon).toHaveBeenCalledTimes(3)
  })

  it('should return 404 for non-existent route', async () => {
    const response = await request(app)
      .get('/api/non-existent')
      .set('Authorization', 'Bearer faketoken')

    expect(response.status).toBe(404)
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
