import type { Request, Response, NextFunction } from 'express'
import jwt from 'jsonwebtoken'

// Extend Express Request to include user
declare global {
  namespace Express {
    interface Request {
      user?: {
        userId: string
        email: string
        role: string
        employee_id: string
      }
    }
  }
}

export const authenticateToken = (
  req: Request,
  res: Response,
  next: NextFunction
) => {
  const authHeader = req.headers.authorization

  // Must be in format: Bearer <token>
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ error: 'Access token required' })
  }

  const token = authHeader.split(' ')[1]

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET!)
    req.user = decoded as any
    next()
  } catch {
    return res.status(403).json({ error: 'Invalid token' })
  }
}