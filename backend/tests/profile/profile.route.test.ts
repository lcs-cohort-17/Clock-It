import { describe, it, expect, vi, beforeEach } from 'vitest'
import request from 'supertest'
import express from 'express'

// ─── MOCK CONTROLLERS ────────────────────────────────────────
vi.mock('../../src/controllers/profileController.js', () => ({
  getProfilesCon: vi.fn((req, res) => res.status(200).json({ success: true })),
  createProfileCon: vi.fn((req, res) => res.status(201).json({ success: true })),
  loginProfileCon: vi.fn((req, res) => res.status(200).json({ success: true })),
  updateProfileCon: vi.fn((req, res) => res.status(200).json({ success: true })),
  deleteProfileCon: vi.fn((req, res) => res.status(200).json({ success: true })),
  resetPasswordCon: vi.fn((req, res) => res.status(200).json({ success: true })),
  updatePasswordCon: vi.fn((req, res) => res.status(200).json({ success: true }))
}))

vi.mock('../../src/middleware/authMiddleware.js', () => ({
  authenticateToken: vi.fn((req, res, next) => next())
}))

import profileRoutes from '../../src/routes/profileRoutes.js'

const app = express()
app.use(express.json())
app.use('/profiles', profileRoutes)

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