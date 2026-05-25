import z from 'zod'
import { Request, Response, NextFunction } from 'express'

export type SubmitLeaveBody = z.infer<typeof submitLeaveSchema>
export type CalendarQuery = z.infer<typeof calendarQuerySchema>
export type UpdateLeaveStatusBody = z.infer<typeof updateLeaveStatusSchema>
export type UpdateLeaveBody = z.infer<typeof updateLeaveBodySchema>

//leave submission system
export const submitLeaveSchema = z.object({
  request_type: z.enum(['leave', 'sick', 'annual', 'unpaid', 'other'], {
    message: 'request_type must be leave, sick, annual, unpaid or other'
  }),
  start_date: z.iso.date('start_date must be a valid date eg 2024-01-01')
    .refine(date => date >= new Date().toISOString().split('T')[0], {
      message: 'start_date must be today or in the future'
    }),
  end_date: z.iso.date('end_date must be a valid date eg 2024-01-01')
    .refine(date => date >= new Date().toISOString().split('T')[0], {
      message: 'end_date must be today or in the future'
    }),
  reason: z
    .string()
    .min(1, 'reason cannot be empty')
    .max(500, 'reason cannot exceed 500 characters'),
})
// double checks logic — end must be on or after start
.refine(data => data.end_date >= data.start_date, {
  message: 'end_date must be on or after start_date',
  path: ['end_date']
})

//Calendar system 
export const calendarQuerySchema = z.object({
  month: z.coerce
    .number({ message: 'month must be a number' })
    .int()
    .min(1, 'month must be between 1 and 12')
    .max(12, 'month must be between 1 and 12')
    .optional(),
  year: z.coerce
    .number({ message: 'year must be a number' })
    .int()
    .min(2000, 'year must be 2000 or later')
    .max(2100, 'year must be 2100 or earlier')
    .optional(),
})

//update leave status
export const updateLeaveStatusSchema = z.object({
  status: z.enum(['approved', 'rejected', 'pending'], {
    message: 'status must be approved, rejected, or pending'
  }),
  comment: z
    .string()
    .max(500, 'comment cannot exceed 500 characters')
    .optional(),
})

export const validate = (schema: z.ZodSchema, target: 'body' | 'query' | 'params' = 'body') => {
  return (req: Request, res: Response, next: NextFunction) => {
    try {
      const result = schema.safeParse(req[target])

      if (!result.success) {
        // flatten() converts Zod's nested error format into a
        // simple { fieldErrors: { fieldName: ['error message'] } }
        // structure that is easy to read on the frontend
        return res.status(400).json({
          message: 'Validation failed',
          errors: result.error.flatten().fieldErrors
        })
      }

      if (target === 'query') {
  Object.assign(req.query, result.data)
} else {
  req[target] = result.data
}
      next()
    } catch (err) {
      console.error('VALIDATE ERROR:', err)
      next(err)
    }
  }
}

export const updateLeaveBodySchema = z.object({
  request_type: z.enum(['leave', 'sick', 'annual', 'unpaid', 'other'], {
    message: 'request_type must be leave, sick, annual, unpaid or other'
  }).optional(),
  start_date: z.iso.date('start_date must be a valid date eg 2024-01-01').optional(),
  end_date: z.iso.date('end_date must be a valid date eg 2024-01-01').optional(),     
  reason: z
    .string()
    .min(1, 'reason cannot be empty')
    .max(500, 'reason cannot exceed 500 characters')
    .optional(),
})
.refine(data => {
  if (data.start_date && data.end_date) {
    return data.end_date >= data.start_date
  }
  return true
}, {
  message: 'end_date must be on or after start_date',
  path: ['end_date']
})
// only check end vs start if both are provided
.refine(data => {
  if (data.start_date && data.end_date) {
    return data.end_date >= data.start_date
  }
  return true
}, {
  message: 'end_date must be on or after start_date',
  path: ['end_date']
})