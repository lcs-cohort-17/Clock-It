import { describe, it, expect, vi, beforeEach } from 'vitest'
import request from 'supertest'
import express from 'express'

// ─── MOCK MODEL ──────────────────────────────────────────────
vi.mock('../../src/models/profileDb.js', () => ({
  getProfilesDb: vi.fn(),
  createProfileDb: vi.fn(),
  updateProfileDb: vi.fn(),
  deleteProfileDb: vi.fn(),
  resetPasswordDb: vi.fn(),
  loginProfileDb: vi.fn()
}))

// ─── MOCK BCRYPT ─────────────────────────────────────────────
vi.mock('bcrypt', () => ({
  default: {
    hash: vi.fn().mockResolvedValue('$2b$10$hashedpassword'),
    compare: vi.fn()
  }
}))

import {
  getProfilesDb,
  // createProfileDb,
  updateProfileDb,
  deleteProfileDb,
  // resetPasswordDb,
  // loginProfileDb
} from '../../src/models/profileDb.js'

import bcrypt from 'bcrypt'

import {
  getProfilesCon,
  // createProfileCon,
  updateProfileCon,
  deleteProfileCon,
  // resetPasswordCon,
  // loginProfileCon
} from '../../src/controllers/profileController.js'

// ─── MINI APP ────────────────────────────────────────────────
const app = express()
app.use(express.json())

app.get('/profiles', getProfilesCon)
// app.post('/profiles', createProfileCon)
app.patch('/profiles/:employee_id', updateProfileCon)
app.delete('/profiles/:employee_id', deleteProfileCon)
// app.patch('/profiles/:employee_id/reset-password', resetPasswordCon)
// app.post('/profiles/login', loginProfileCon)

beforeEach(() => {
  vi.clearAllMocks()
})

// ─── GET ALL ─────────────────────────────────────────────────
describe('getProfilesCon', () => {

  it('should return 200 with all profiles', async () => {
    vi.mocked(getProfilesDb).mockResolvedValueOnce({
      success: true,
      data: [
        { id: '1', first_name: 'Joshua', last_name: 'Jacobs', employee_id: 'S-005', role: 'staff', is_active: true, email: 'jodam@gmail.com', password: 'hashed' },
        { id: '2', first_name: 'Sarah', last_name: 'Johnson', employee_id: 'A-010', role: 'admin', is_active: true, email: 'sarah@company.com', password: 'hashed' }
      ]
    })

    const response = await request(app).get('/profiles')

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)
    expect(response.body.data).toHaveLength(2)
  })

  it('should return 400 when model fails', async () => {
    vi.mocked(getProfilesDb).mockResolvedValueOnce({
      success: false,
      error: 'Database error'
    })

    const response = await request(app).get('/profiles')

    expect(response.status).toBe(400)
    expect(response.body.success).toBe(false)
  })

  it('should return 500 on unexpected crash', async () => {
    vi.mocked(getProfilesDb).mockRejectedValueOnce(new Error('Crash'))

    const response = await request(app).get('/profiles')

    expect(response.status).toBe(500)
  })
})

// ─── CREATE ──────────────────────────────────────────────────
// describe('createProfileCon', () => {

//   it('should return 201 with new profile and plain text password', async () => {
//     vi.mocked(createProfileDb).mockResolvedValueOnce({
//       success: true,
//       data: {
//         id: '1',
//         first_name: 'Joshua',
//         last_name: 'Jacobs',
//         employee_id: 'S-005',
//         role: 'staff',
//         is_active: true,
//         email: 'jodam@gmail.com',
//         password: 'Xk9mP2qR'  // plain text returned to admin
//       }
//     })

//     const response = await request(app)
//       .post('/profiles')
//       .send({
//         first_name: 'Joshua',
//         last_name: 'Jacobs',
//         employee_id: 'S-005',
//         role: 'staff',
//         email: 'jodam@gmail.com'
//       })

//     expect(response.status).toBe(201)
//     expect(response.body.success).toBe(true)
//     // Plain text password must be in response for admin to share
//     expect(response.body.data.password).toHaveLength(8)
//   })

//   it('should return 400 when required fields are missing', async () => {
//     const response = await request(app)
//       .post('/profiles')
//       .send({ first_name: 'Joshua' })  // missing fields

//     expect(response.status).toBe(400)
//     expect(response.body.success).toBe(false)
//     expect(response.body.error).toBe('All fields are required')
//   })

//   it('should return 500 on unexpected crash', async () => {
//     vi.mocked(createProfileDb).mockRejectedValueOnce(new Error('Crash'))

//     const response = await request(app)
//       .post('/profiles')
//       .send({
//         first_name: 'Joshua',
//         last_name: 'Jacobs',
//         employee_id: 'S-005',
//         role: 'staff',
//         email: 'jodam@gmail.com'
//       })

//     expect(response.status).toBe(500)
//   })
// })

// ─── LOGIN ───────────────────────────────────────────────────
// describe('loginProfileCon', () => {

//   it('should return 200 with token on successful login', async () => {
//     vi.mocked(loginProfileDb).mockResolvedValueOnce({
//       success: true,
//       data: {
//         id: '1',
//         first_name: 'Joshua',
//         last_name: 'Jacobs',
//         employee_id: 'S-005',
//         role: 'staff',
//         is_active: true,
//         email: 'jodam@gmail.com',
//         password: '$2b$10$hashedpassword'
//       }
//     })

//     // bcrypt.compare returns true — password matches
//     vi.mocked(bcrypt.compare).mockResolvedValueOnce(true as never)

//     const response = await request(app)
//       .post('/profiles/login')
//       .send({ email: 'jodam@gmail.com', password: 'Xk9mP2qR' })

//     expect(response.status).toBe(200)
//     expect(response.body.success).toBe(true)
//     // Token must be in response
//     expect(response.body.token).toBeDefined()
//     // User info returned — no password in response
//     expect(response.body.user.email).toBe('jodam@gmail.com')
//     expect(response.body.user.password).toBeUndefined()
//   })

//   it('should return 401 when password does not match', async () => {
//     vi.mocked(loginProfileDb).mockResolvedValueOnce({
//       success: true,
//       data: {
//         id: '1',
//         email: 'jodam@gmail.com',
//         password: '$2b$10$hashedpassword',
//         is_active: true,
//         role: 'staff',
//         first_name: 'Joshua',
//         last_name: 'Jacobs',
//         employee_id: 'S-005'
//       }
//     })

//     // bcrypt.compare returns false — wrong password
//     vi.mocked(bcrypt.compare).mockResolvedValueOnce(false as never)

//     const response = await request(app)
//       .post('/profiles/login')
//       .send({ email: 'jodam@gmail.com', password: 'wrongpassword' })

//     expect(response.status).toBe(401)
//     expect(response.body.error).toBe('Invalid email or password')
//   })

//   it('should return 401 when email not found', async () => {
//     vi.mocked(loginProfileDb).mockResolvedValueOnce({
//       success: false,
//       error: 'User not found'
//     })

//     const response = await request(app)
//       .post('/profiles/login')
//       .send({ email: 'ghost@gmail.com', password: 'anything' })

//     expect(response.status).toBe(401)
//     expect(response.body.error).toBe('Invalid email or password')
//   })

//   it('should return 400 when fields are missing', async () => {
//     const response = await request(app)
//       .post('/profiles/login')
//       .send({ email: 'jodam@gmail.com' })  // missing password

//     expect(response.status).toBe(400)
//     expect(response.body.error).toBe('Email and password are required')
//   })
// })

// ─── UPDATE ──────────────────────────────────────────────────
describe('updateProfileCon', () => {

  it('should return 200 on successful update', async () => {
    vi.mocked(updateProfileDb).mockResolvedValueOnce({
      success: true,
      data: {
        id: '1',
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
    vi.mocked(updateProfileDb).mockResolvedValueOnce({
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
    vi.mocked(updateProfileDb).mockRejectedValueOnce(new Error('Crash'))

    const response = await request(app)
      .patch('/profiles/S-007')
      .send({ first_name: 'Ghost' })

    expect(response.status).toBe(500)
  })
})

// ─── DELETE ──────────────────────────────────────────────────
describe('deleteProfileCon', () => {

  it('should return 200 on successful soft delete', async () => {
    vi.mocked(deleteProfileDb).mockResolvedValueOnce({
      success: true,
      message: 'profile deleted successfully'
    })

    const response = await request(app).delete('/profiles/S-007')

    expect(response.status).toBe(200)
    expect(response.body.success).toBe(true)
    expect(response.body.message).toBe('profile deleted successfully')
  })

  it('should return 400 when model fails', async () => {
    vi.mocked(deleteProfileDb).mockResolvedValueOnce({
      success: false,
      error: 'Delete failed'
    })

    const response = await request(app).delete('/profiles/S-007')

    expect(response.status).toBe(400)
    expect(response.body.error).toBe('Delete failed')
  })

  it('should return 500 on unexpected crash', async () => {
    vi.mocked(deleteProfileDb).mockRejectedValueOnce(new Error('Crash'))

    const response = await request(app).delete('/profiles/S-007')

    expect(response.status).toBe(500)
  })
})

// ─── RESET PASSWORD ──────────────────────────────────────────
// describe('resetPasswordCon', () => {

//   it('should return 200 with new plain text password', async () => {
//     vi.mocked(resetPasswordDb).mockResolvedValueOnce({
//       success: true,
//       data: {
//         id: '1',
//         employee_id: 'S-007',
//         password: 'Nq7rT2mX',  // new plain text password
//         first_name: 'Siza',
//         last_name: 'Mpafa',
//         role: 'staff',
//         is_active: true,
//         email: 'siza@gmail.com'
//       }
//     })

//     const response = await request(app)
//       .patch('/profiles/S-007/reset-password')

//     expect(response.status).toBe(200)
//     expect(response.body.success).toBe(true)
//     // New password returned to admin in plain text
//     expect(response.body.data.password).toHaveLength(8)
//   })

//   it('should return 400 when model fails', async () => {
//     vi.mocked(resetPasswordDb).mockResolvedValueOnce({
//       success: false,
//       error: 'Reset failed'
//     })

//     const response = await request(app)
//       .patch('/profiles/S-007/reset-password')

//     expect(response.status).toBe(400)
//     expect(response.body.error).toBe('Reset failed')
//   })

//   it('should return 500 on unexpected crash', async () => {
//     vi.mocked(resetPasswordDb).mockRejectedValueOnce(new Error('Crash'))

//     const response = await request(app)
//       .patch('/profiles/S-007/reset-password')

//     expect(response.status).toBe(500)
//   })
// })