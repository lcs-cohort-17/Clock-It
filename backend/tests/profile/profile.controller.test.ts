import { describe, it, expect, vi, beforeEach } from 'vitest'
import request from 'supertest'
import express from 'express'

// ─── MOCK MODEL — ONE vi.mock ONLY ───────────────────────────
vi.mock('../../src/models/profileDb.js', () => ({
  adminGettingAllUsersDb: vi.fn(),
  adminCreatingUserDb: vi.fn(),
  adminUpdatingUserDb: vi.fn(),
  adminDeletingUserDb: vi.fn(),
  resetPasswordDb: vi.fn(),
  loginProfileDb: vi.fn(),
  getProfileByIdDb: vi.fn(),
  updatePasswordDb: vi.fn()
}))

// ─── MOCK BCRYPT ─────────────────────────────────────────────
vi.mock('bcrypt', () => ({
  default: {
    hash: vi.fn().mockResolvedValue('$2b$10$hashedpassword'),
    compare: vi.fn()
  }
}))

// ─── MOCK JWT ────────────────────────────────────────────────
vi.mock('jsonwebtoken', () => ({
  default: {
    sign: vi.fn().mockReturnValue('mock-jwt-token')
  }
}))

import {
  getProfileByIdDb,
  adminGettingAllUsersDb,
  adminUpdatingUserDb,
  adminDeletingUserDb,
  adminCreatingUserDb,
  resetPasswordDb,
  loginProfileDb,
  updatePasswordDb
} from '../../src/models/profileDb.js'

import bcrypt from 'bcrypt'

import {
  adminGettingAllUsersCon,
  adminUpdatingUserCon,
  adminDeletingUserCon,
  adminCreatingUserCon,
  resetPasswordCon,
  loginProfileCon,
  getProfileByIdCon,
  updatePasswordCon
} from '../../src/controllers/profileController.js'

// ─── MINI APP ────────────────────────────────────────────────
const app = express()
app.use(express.json())

// Fake auth — inject req.user so controllers that use req.user work
app.use((req: any, res, next) => {
  req.user = {
    userId: '14271887-48ea-48c8-9890-6cb196afa0Gc',
    email: 'officialstaff@clockit.com',
    role: 'staff',
    employee_id: 'S-300'
  }
  next()
})

// Order matters — specific routes before param routes
app.get('/profiles', adminGettingAllUsersCon)
app.post('/profiles/login', loginProfileCon)
app.post('/profiles', adminCreatingUserCon)
app.patch('/profiles/:employee_id/reset-password', resetPasswordCon)
app.patch('/profiles/:employee_id/update-password', updatePasswordCon)
app.patch('/profiles/:employee_id', adminUpdatingUserCon)
app.delete('/profiles/:employee_id', adminDeletingUserCon)
app.get('/profiles/:employee_id', getProfileByIdCon)

beforeEach(() => {
  vi.clearAllMocks()
  process.env.JWT_SECRET = 'test-secret'
})

// ─── GET ALL ─────────────────────────────────────────────────
describe('adminGettingAllUsersCon', () => {

  it('should return 200 with all profiles', async () => {
    vi.mocked(adminGettingAllUsersDb).mockResolvedValueOnce({
      success: true,
      data: [
        { id: '14271887-48ea-48c8-9890-6cb196afa0Gc', first_name: 'Joshua', last_name: 'Jacobs', employee_id: 'S-005', role: 'staff', is_active: true, email: 'jodam@gmail.com', password: 'hashed' },
        { id: '14361887-48ea-48c8-9890-6cb196afa0Gc', first_name: 'Sarah', last_name: 'Johnson', employee_id: 'A-010', role: 'admin', is_active: true, email: 'sarah@company.com', password: 'hashed' }
      ]
    })

    const response = await request(app).get('/profiles')

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)
    expect(response.body.data).toHaveLength(2)
  })

  it('should return 400 when model fails', async () => {
    vi.mocked(adminGettingAllUsersDb).mockResolvedValueOnce({
      success: false,
      error: 'Database error'
    })

    const response = await request(app).get('/profiles')

    expect(response.status).toBe(400)
    expect(response.body.success).toBe(false)
  })

  it('should return 500 on unexpected crash', async () => {
    vi.mocked(adminGettingAllUsersDb).mockRejectedValueOnce(new Error('Crash'))

    const response = await request(app).get('/profiles')

    expect(response.status).toBe(500)
  })
})

// ─── CREATE ──────────────────────────────────────────────────
describe('adminCreatingUserCon', () => {

  it('should return 201 with new profile and plain text password to admin', async () => {
    vi.mocked(adminCreatingUserDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
        first_name: 'Joshua',
        last_name: 'Jacobs',
        employee_id: 'S-005',
        role: 'staff',
        is_active: true,
        email: 'jodam@gmail.com',
        password: 'Xk9mP2qR'
      }
    })

    const response = await request(app)
      .post('/profiles')
      .send({
        first_name: 'Joshua',
        last_name: 'Jacobs',
        employee_id: 'S-005',
        role: 'staff',
        email: 'jodam@gmail.com'
      })

    expect(response.status).toBe(201)
    expect(response.body.success).toBe(true)
    expect(response.body.data.password).toHaveLength(8)
    expect(response.body.data.password).not.toMatch(/^\$2[ab]\$/)
    expect(response.body.data.first_name).toBe('Joshua')
    expect(response.body.data.employee_id).toBe('S-005')
    expect(response.body.data.role).toBe('staff')
  })

  it('should return 201 when admin creates another admin', async () => {
    vi.mocked(adminCreatingUserDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '24271887-48ea-48c8-9890-6cb196afa0Gc',
        first_name: 'Sarah',
        last_name: 'Johnson',
        employee_id: 'A-010',
        role: 'admin',
        is_active: true,
        email: 'sarah@company.com',
        password: 'Nq7rT2mX'
      }
    })

    const response = await request(app)
      .post('/profiles')
      .send({
        first_name: 'Sarah',
        last_name: 'Johnson',
        employee_id: 'A-010',
        role: 'admin',
        email: 'sarah@company.com'
      })

    expect(response.status).toBe(201)
    expect(response.body.success).toBe(true)
    expect(response.body.data.role).toBe('admin')
    expect(response.body.data.password).toHaveLength(8)
  })

  it('should return 400 when required fields are missing', async () => {
    const response = await request(app)
      .post('/profiles')
      .send({ first_name: 'Joshua' })

    expect(response.status).toBe(400)
    expect(response.body.success).toBe(false)
    expect(response.body.error).toBe('All fields are required')
  })

  it('should return 400 when model returns error', async () => {
    vi.mocked(adminCreatingUserDb).mockResolvedValueOnce({
      success: false,
      error: 'Staff employee_id must start with S-'
    })

    const response = await request(app)
      .post('/profiles')
      .send({
        first_name: 'Joshua',
        last_name: 'Jacobs',
        employee_id: 'A-005',
        role: 'staff',
        email: 'jodam@gmail.com'
      })

    expect(response.status).toBe(400)
    expect(response.body.error).toBe('Staff employee_id must start with S-')
  })

  it('should return 500 on unexpected crash', async () => {
    vi.mocked(adminCreatingUserDb).mockRejectedValueOnce(new Error('Crash'))

    const response = await request(app)
      .post('/profiles')
      .send({
        first_name: 'Joshua',
        last_name: 'Jacobs',
        employee_id: 'S-005',
        role: 'staff',
        email: 'jodam@gmail.com'
      })

    expect(response.status).toBe(500)
    expect(response.body.success).toBe(false)
  })
})

// ─── LOGIN ───────────────────────────────────────────────────
describe('loginProfileCon', () => {

  it('should return 200 with token on successful login', async () => {
    vi.mocked(loginProfileDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
        first_name: 'Joshua',
        last_name: 'Jacobs',
        employee_id: 'S-005',
        role: 'staff',
        is_active: true,
        email: 'jodam@gmail.com',
        password: '$2b$10$hashedpassword'
      }
    })

    vi.mocked(bcrypt.compare).mockResolvedValueOnce(true as never)

    vi.mocked(getProfileByIdDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
        first_name: 'joshua',
        last_name: 'Jacobs',
        employee_id: 'S-005',
        role: 'staff',
        is_active: true,
        email: 'jodam@gmail.com',
        password: '$2b$10$hashedpassword'
      }
    })

    const response = await request(app)
      .post('/profiles/login')
      .send({ email: 'jodam@gmail.com', password: 'Xk9mP2qR' })

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)
    expect(response.body.token).toBeDefined()
    expect(response.body.first_name).toBe('Joshua')
    expect(response.body.user.email).toBe('jodam@gmail.com')
    expect(response.body.user.password).toBeUndefined()
  })

  it('should return 401 when password does not match', async () => {
    vi.mocked(loginProfileDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
        email: 'jodam@gmail.com',
        password: '$2b$10$hashedpassword',
        is_active: true,
        role: 'staff',
        first_name: 'Joshua',
        last_name: 'Jacobs',
        employee_id: 'S-005'
      }
    })

    vi.mocked(bcrypt.compare).mockResolvedValueOnce(false as never)

    const response = await request(app)
      .post('/profiles/login')
      .send({ email: 'jodam@gmail.com', password: 'wrongpassword' })

    expect(response.status).toBe(401)
    expect(response.body.error).toBe('Invalid email or password')
  })

  it('should return 401 when email not found', async () => {
    vi.mocked(loginProfileDb).mockResolvedValueOnce({
      success: false,
      error: 'User not found'
    })

    const response = await request(app)
      .post('/profiles/login')
      .send({ email: 'ghost@gmail.com', password: 'anything' })

    expect(response.status).toBe(401)
    expect(response.body.error).toBe('Invalid email or password')
  })

  it('should return 400 when fields are missing', async () => {
    const response = await request(app)
      .post('/profiles/login')
      .send({ email: 'jodam@gmail.com' })

    expect(response.status).toBe(400)
    expect(response.body.error).toBe('Email and password are required')
  })

  it('should return first_name capitalized in login response', async () => {
    vi.mocked(loginProfileDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
        first_name: 'joshua',
        last_name: 'Jacobs',
        employee_id: 'S-005',
        role: 'staff',
        is_active: true,
        email: 'jodam@gmail.com',
        password: '$2b$10$hashedpassword'
      }
    })

    vi.mocked(bcrypt.compare).mockResolvedValueOnce(true as never)

    vi.mocked(getProfileByIdDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
        first_name: 'joshua',
        last_name: 'Jacobs',
        employee_id: 'S-005',
        role: 'staff',
        is_active: true,
        email: 'jodam@gmail.com',
        password: '$2b$10$hashedpassword'
      }
    })

    const response = await request(app)
      .post('/profiles/login')
      .send({ email: 'jodam@gmail.com', password: 'Xk9mP2qR' })

    expect(response.status).toBe(200)
    expect(response.body).toHaveProperty('first_name')
    expect(response.body.first_name).toBe('Joshua')
  })
})

// ─── UPDATE ──────────────────────────────────────────────────
describe('adminUpdatingUserCon', () => {

  it('should return 200 on successful update', async () => {
    vi.mocked(adminUpdatingUserDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '18741887-48ea-48c8-9890-6cb196afa0Gc',
        first_name: 'Siza',
        last_name: 'Mpafa',
        employee_id: 'S-007',
        role: 'staff',
        is_active: true,
        email: 'siza@gmail.com',
        password: 'hashed'
      }
    })

    const response = await request(app)
      .patch('/profiles/S-007')
      .send({ first_name: 'Siza' })

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)
  })

  it('should return 400 when model fails', async () => {
    vi.mocked(adminUpdatingUserDb).mockResolvedValueOnce({
      success: false,
      error: 'Update failed'
    })

    const response = await request(app)
      .patch('/profiles/S-007')
      .send({ first_name: 'Ghost' })

    expect(response.status).toBe(400)
    expect(response.body.error).toBe('Update failed')
  })

  it('should return 500 on unexpected crash', async () => {
    vi.mocked(adminUpdatingUserDb).mockRejectedValueOnce(new Error('Crash'))

    const response = await request(app)
      .patch('/profiles/S-007')
      .send({ first_name: 'Ghost' })

    expect(response.status).toBe(500)
  })
})

// ─── DELETE ──────────────────────────────────────────────────
describe('adminDeletingUserCon', () => {

  it('should return 200 on successful soft delete', async () => {
    vi.mocked(adminDeletingUserDb).mockResolvedValueOnce({
      success: true,
      message: 'profile deleted successfully'
    })

    const response = await request(app).delete('/profiles/S-007')

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)
    expect(response.body.message).toBe('profile deleted successfully')
  })

  it('should return 400 when model fails', async () => {
    vi.mocked(adminDeletingUserDb).mockResolvedValueOnce({
      success: false,
      error: 'Delete failed'
    })

    const response = await request(app).delete('/profiles/S-007')

    expect(response.status).toBe(400)
    expect(response.body.error).toBe('Delete failed')
  })

  it('should return 500 on unexpected crash', async () => {
    vi.mocked(adminDeletingUserDb).mockRejectedValueOnce(new Error('Crash'))

    const response = await request(app).delete('/profiles/S-007')

    expect(response.status).toBe(500)
  })
})

// ─── RESET PASSWORD ──────────────────────────────────────────
describe('resetPasswordCon', () => {

  it('should return 200 with new plain text password', async () => {
    vi.mocked(resetPasswordDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '18741887-48ea-48c8-9890-6cb196afa0Gc',
        employee_id: 'S-007',
        password: 'Nq7rT2mX',
        first_name: 'Siza',
        last_name: 'Mpafa',
        role: 'staff',
        is_active: true,
        email: 'siza@gmail.com'
      }
    })

    const response = await request(app).patch('/profiles/S-007/reset-password')

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)
    expect(response.body.data.password).toHaveLength(8)
  })

  it('should return 400 when model fails', async () => {
    vi.mocked(resetPasswordDb).mockResolvedValueOnce({
      success: false,
      error: 'Reset failed'
    })

    const response = await request(app).patch('/profiles/S-007/reset-password')

    expect(response.status).toBe(400)
    expect(response.body.error).toBe('Reset failed')
  })

  it('should return 500 on unexpected crash', async () => {
    vi.mocked(resetPasswordDb).mockRejectedValueOnce(new Error('Crash'))

    const response = await request(app).patch('/profiles/S-007/reset-password')

    expect(response.status).toBe(500)
  })
})

// ─── UPDATE PASSWORD ─────────────────────────────────────────
describe('updatePasswordCon', () => {

  it('should return 200 when password updated successfully', async () => {
    vi.mocked(loginProfileDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
        employee_id: 'S-300',
        password: '$2b$10$hashedoldpassword',
        first_name: 'Official',
        last_name: 'Staff',
        role: 'staff',
        is_active: true,
        email: 'officialstaff@clockit.com'
      }
    })

    vi.mocked(bcrypt.compare).mockResolvedValueOnce(true as never)

    vi.mocked(updatePasswordDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
        employee_id: 'S-300',
        password: '$2b$10$hashednewpassword',
        first_name: 'Official',
        last_name: 'Staff',
        role: 'staff',
        is_active: true,
        email: 'officialstaff@clockit.com'
      }
    })

    const response = await request(app)
      .patch('/profiles/S-300/update-password')
      .send({ oldPassword: 'IUsW0l4r', newPassword: 'selfcreatedpassword' })

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)
    expect(response.body.message).toBe('Password updated successfully')
  })

  it('should return 400 when old or new password missing', async () => {
    const response = await request(app)
      .patch('/profiles/S-300/update-password')
      .send({ oldPassword: 'IUsW0l4r' })

    expect(response.status).toBe(400)
    expect(response.body.error).toBe('Old password and new password are required')
  })

  it('should return 401 when old password is incorrect', async () => {
    vi.mocked(loginProfileDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
        employee_id: 'S-300',
        password: '$2b$10$hashedoldpassword',
        first_name: 'Official',
        last_name: 'Staff',
        role: 'staff',
        is_active: true,
        email: 'officialstaff@clockit.com'
      }
    })

    vi.mocked(bcrypt.compare).mockResolvedValueOnce(false as never)

    const response = await request(app)
      .patch('/profiles/S-300/update-password')
      .send({ oldPassword: 'wrongpassword', newPassword: 'newpassword' })

    expect(response.status).toBe(401)
    expect(response.body.error).toBe('Old password is incorrect')
  })

  it('should return 404 when user not found', async () => {
    vi.mocked(loginProfileDb).mockResolvedValueOnce({
      success: false,
      error: 'User not found'
    })

    const response = await request(app)
      .patch('/profiles/X-999/update-password')
      .send({ oldPassword: 'anything', newPassword: 'newpassword' })

    expect(response.status).toBe(404)
    expect(response.body.error).toBe('User not found')
  })

  it('should return 500 on unexpected crash', async () => {
    vi.mocked(loginProfileDb).mockRejectedValueOnce(new Error('Crash'))

    const response = await request(app)
      .patch('/profiles/S-300/update-password')
      .send({ oldPassword: 'anything', newPassword: 'newpassword' })

    expect(response.status).toBe(500)
  })
})

// ─── GET BY ID ───────────────────────────────────────────────
describe('getProfileByIdCon', () => {

  it('should return 200 with one profile', async () => {
    vi.mocked(getProfileByIdDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14361887-48ea-48c8-9890-6cb196afa0Gc',
        first_name: 'Sarah',
        last_name: 'Johnson',
        employee_id: 'S-006',
        email: 'sarah@company.com',
        role: 'staff',
        is_active: true,
        password: 'hashed'
      }
    })

    const response = await request(app).get('/profiles/S-006')

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)
    expect(response.body.data.first_name).toBe('Sarah')
    expect(response.body.data.last_name).toBe('Johnson')
    expect(response.body.data.email).toBe('sarah@company.com')
    expect(response.body.data.password).toBeUndefined()
  })

  it('should call the model with employee_id from params', async () => {
    vi.mocked(getProfileByIdDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '14361887-48ea-48c8-9890-6cb196afa0Gc',
        first_name: 'Sarah',
        last_name: 'Johnson',
        employee_id: 'S-006',
        email: 'sarah@company.com',
        role: 'staff',
        is_active: true,
        password: 'hashed'
      }
    })

    await request(app).get('/profiles/S-006')

    expect(getProfileByIdDb).toHaveBeenCalledWith('S-006')
  })

  it('should return 400 when profile not found', async () => {
    vi.mocked(getProfileByIdDb).mockResolvedValueOnce({
      success: false,
      error: 'Profile not found'
    })

    const response = await request(app).get('/profiles/X-999')

    expect(response.status).toBe(400)
    expect(response.body.error).toBe('Profile not found')
  })

  it('should return 500 on unexpected crash', async () => {
    vi.mocked(getProfileByIdDb).mockRejectedValueOnce(new Error('Database connection failed'))

    const response = await request(app).get('/profiles/S-006')

    expect(response.status).toBe(500)
    expect(response.body.error).toBe('Database connection failed')
  })

  it('should handle null first_name from database', async () => {
    vi.mocked(getProfileByIdDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '18741887-48ea-48c8-9890-6cb196afa0Gc',
        first_name: null,
        last_name: 'Smith',
        employee_id: 'S-789',
        email: 'jane@company.com',
        role: 'staff',
        is_active: true,
        password: 'hashed'
      } as any
    })

    const response = await request(app).get('/profiles/S-789')

    expect(response.status).toBe(200)
    expect(response.body.data.first_name).toBeNull()
  })
})