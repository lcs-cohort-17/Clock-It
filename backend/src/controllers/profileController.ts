import type { Request, Response } from 'express'
import bcrypt from 'bcrypt'
import jwt from 'jsonwebtoken'
import {
  getProfilesDb,
  getProfileByIdDb,
  updateProfileDb,
  deleteProfileDb,
  loginProfileDb,
  createProfileDb,
  updatePasswordDb,
  resetPasswordDb,
} from '../models/profileDb.js'

// ─── GET ALL ─────────────────────────────────────────────────
export const getProfilesCon = async (req: Request, res: Response) => {
  try {
    const result = await getProfilesDb()

    if (!result.success) {
      return res.status(400).json(result)
    }

    return res.status(200).json(result)
  } catch (error: any) {
    return res.status(500).json({ success: false, error: error.message })
  }
}

export const getProfileByIdCon = async (req: Request, res: Response) => {
  try {
    const employee_id = req.params.employee_id as string

    const result = await getProfileByIdDb(employee_id)

    if (!result.success) {
      return res.status(400).json({
        success: false,
        error: result.error
      })
    }

    // SO THAT SENSITIVE FIELDS ARE NOT SENT
    const safeData = { ...result.data }
    delete (safeData as any).password
    delete (safeData as any).password_hash
    delete (safeData as any).reset_token

    return res.status(200).json({
      success: true,
      data: safeData
    })
  } catch (error: any) {
    return res.status(500).json({ 
      success: false, 
      error: error.message 
    })
  }
}

// ─── CREATE ──────────────────────────────────────────────────
// Admin creates staff or other admin
// Password is auto-generated — plain text returned to admin (ticket 031)
export const createProfileCon = async (req: Request, res: Response) => {
  try {
    const { first_name, last_name, employee_id, role, email } = req.body

    if (!first_name || !last_name || !employee_id || !role || !email) {
      return res.status(400).json({
        success: false,
        error: 'All fields are required'
      })
    }

    const result = await createProfileDb(
      first_name,
      last_name,
      employee_id,
      role,
      email
    )

    if (!result.success) {
      return res.status(400).json(result)
    }

    // Return plain text password to admin — they share it with staff
    return res.status(201).json(result)
  } catch (error: any) {
    return res.status(500).json({ success: false, error: error.message })
  }
}

// ─── LOGIN ───────────────────────────────────────────────────
// bcrypt.compare lives here — controller handles auth logic
// Model just fetches the user from DB

function capitalizeFirstName(name: string | null): string | null {
  if (!name) return name
  return name.charAt(0).toUpperCase() + name.slice(1).toLowerCase()
}

export const loginProfileCon = async (req: Request, res: Response) => {
  try {
    const { email, password } = req.body

    if (!email || !password) {
      return res.status(400).json({
        success: false,
        error: 'Email and password are required'
      })
    }

    // Get user from DB — model returns hashed password
    const result = await loginProfileDb(email)

    if (!result.success) {
      // Don't expose whether email exists — security best practice
      return res.status(401).json({
        success: false,
        error: 'Invalid email or password'
      })
    }

    // Compare plain text input against hashed password in DB
    const passwordMatch = await bcrypt.compare(password, result.data!.password)

    if (!passwordMatch) {
      return res.status(401).json({
        success: false,
        error: 'Invalid email or password'
      })
    }

    // Generate JWT token — expires in 2 hours
    const token = jwt.sign(
      {
        userId: result.data!.id,
        email: result.data!.email,
        role: result.data!.role,
        employee_id: result.data!.employee_id
      },
      process.env.JWT_SECRET!,
      { expiresIn: '2h' }
    )

    const profileResult = await getProfileByIdDb(result.data!.employee_id)

    const first_name = profileResult.success && profileResult.data?.first_name
      ? capitalizeFirstName(profileResult.data.first_name)
      : null

    // Return token and user info — never return password
    return res.status(200).json({
      success: true,
      token,
      first_name,
      user: {
        id: result.data!.id,
        first_name,
        last_name: result.data!.last_name,
        email: result.data!.email,
        employee_id: result.data!.employee_id,
        role: result.data!.role
        // password intentionally omitted
      }
    })
  } catch (error: any) {
    return res.status(500).json({ success: false, error: error.message })
  }
}

// ─── UPDATE ──────────────────────────────────────────────────
export const updateProfileCon = async (req: Request, res: Response) => {
  try {
    const  employee_id  = req.params.employee_id as string
    const updates = req.body

    const result = await updateProfileDb(employee_id, updates)

    if (!result.success) {
      return res.status(400).json(result)
    }

    return res.status(200).json(result)
  } catch (error: any) {
    return res.status(500).json({ success: false, error: error.message })
  }
}

// ─── DELETE (SOFT) ───────────────────────────────────────────
export const deleteProfileCon = async (req: Request, res: Response) => {
  try {
    const  employee_id  = req.params.employee_id as string

    const result = await deleteProfileDb(employee_id)

    if (!result.success) {
      return res.status(400).json(result)
    }

    return res.status(200).json(result)
  } catch (error: any) {
    return res.status(500).json({ success: false, error: error.message })
  }
}

// // ─── RESET PASSWORD ──────────────────────────────────────────
// // Admin resets — generates new 8 char plain text, returns it
// // This is PATCH not POST — updating existing password
export const resetPasswordCon = async (req: Request, res: Response) => {
  try {
    const employee_id = req.params.employee_id as string

    const result = await resetPasswordDb(employee_id)

    if (!result.success) {
      return res.status(400).json(result)
    }

    return res.status(200).json(result)
  } catch (error: any) {
    return res.status(500).json({ success: false, error: error.message })
  }
}

export const updatePasswordCon = async (req: Request, res: Response) => {
  try {
    const employee_id = req.params.employee_id as string
    const { oldPassword, newPassword } = req.body

    if (!oldPassword || !newPassword) {
      return res.status(400).json({
        success: false,
        error: 'Old password and new password are required'
      })
    }

    // Get user first to verify old password
    const userResult = await loginProfileDb(req.user!.email)
    if (!userResult.success) {
      return res.status(404).json({ success: false, error: 'User not found' })
    }

    // Verify old password — bcrypt.compare in controller
    const passwordMatch = await bcrypt.compare(oldPassword, userResult.data!.password)
    if (!passwordMatch) {
      return res.status(401).json({
        success: false,
        error: 'Old password is incorrect'
      })
    }

    // Hash new password — controller hashes, model just stores
    const hashedPassword = await bcrypt.hash(newPassword, 10)

    const result = await updatePasswordDb(employee_id, hashedPassword)

    if (!result.success) {
      return res.status(400).json(result)
    }

    return res.status(200).json({ success: true, message: 'Password updated successfully' })
  } catch (error: any) {
    return res.status(500).json({ success: false, error: error.message })
  }
}



