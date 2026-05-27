import { Request, Response } from 'express';
import { insertLeaveRequest,
   fetchCalendar,
    updateLeaveRequestStatus,
    fetchAttendanceRecords,
    findLeaveById,
    findActiveProfile,
    updateLeaveRequest
   } from '../models/leaveDb.js';

// Stub controller functions
interface AuthRequest extends Request {
  auth?: {
    userId: string
    role: string
    token?: string
  }
}

//submitLeave 
export const submitLeave = async (req: AuthRequest, res: Response) => {
  if (!req.auth) return res.status(401).json({ message: 'Unauthorized' })
  const profileId = req.auth.userId

  // check user exists and is active before inserting
  const { data: profile, error: profileError } = await findActiveProfile(profileId)
  if (profileError || !profile) {
    return res.status(403).json({ message: 'User not found or inactive' })
  }

  const { request_type, start_date, end_date, reason } = req.body

  const { data, error } = await insertLeaveRequest(profileId, {
    request_type, start_date, end_date, reason
  })

  if (error) return res.status(500).json({ error })
  return res.status(201).json({ data })
}


//Calender 
export const getCalendar = async (req: AuthRequest, res: Response) => {
  if (!req.auth) return res.status(401).json({ message: 'Unauthorized' })
  const profileId = req.auth.userId

  const { month, year } = req.query as { month?: number; year?: number }

  let from: string | undefined
  let to: string | undefined

  if (month && year) {
    from = `${year}-${String(month).padStart(2, '0')}-01`
    to = new Date(year, month, 0).toISOString().split('T')[0]
  }

  const { data, error } = await fetchCalendar(profileId, req.auth.role, from, to)
  if (error) return res.status(500).json({ error })

  // group by date to match Calendar type and ticket requirement
  const grouped = (data ?? []).reduce((acc: Record<string, any[]>, request: any) => {
    const date = request.start_date.split('T')[0]
    if (!acc[date]) acc[date] = []
    acc[date].push(request)
    return acc
  }, {})

  return res.status(200).json({ data: grouped })
}

// get leave
export const getLeave = async (req: AuthRequest, res: Response) => {
  if (!req.auth) return res.status(401).json({ message: 'Unauthorized' })
  const profileId = req.auth.userId      

  const page = Number(req.query.page) || 1
  const pageSize = Number(req.query.pageSize) || 10
  const status = req.query.status as string | undefined
  const type = req.query.type as string | undefined

  const { data, error, count } = await fetchAttendanceRecords(
    profileId, req.auth.role, { status, type, page, pageSize }
  )

  if (error) return res.status(500).json({ error })
  return res.status(200).json({ data, count })
}

//Update leave 
export const updateLeaveStatus = async (req: AuthRequest, res: Response) => {
  if (!req.auth || req.auth.role !== 'admin') {
    return res.status(403).json({ message: 'Forbidden' })
  }
  
  const id = req.params.id as string                            // params are always strings, no Array.isArray needed
  if (!id) return res.status(400).json({ message: 'Leave request id missing' })

  // check leave exists before updating
  const { data: existing, error: findError } = await findLeaveById(id)
  if (findError || !existing) {
    return res.status(404).json({ message: 'Leave request not found' })
  }

  const { status } = req.body
  const { data, error } = await updateLeaveRequestStatus(id, status)

  if (error) return res.status(500).json({ error })
  return res.status(200).json({ data })
}

// Update leave details — admin edits any leave request
export const updateLeave = async (req: AuthRequest, res: Response) => {
  if (!req.auth || req.auth.role !== 'admin') {
    return res.status(403).json({ message: 'Forbidden' })
  }

  const id = req.params.id as string
  if (!id) return res.status(400).json({ message: 'Leave request id missing' })

  // check leave exists
  const { data: existing, error: findError } = await findLeaveById(id)
  if (findError || !existing) {
    return res.status(404).json({ message: 'Leave request not found' })
  }

  const { request_type, start_date, end_date, reason } = req.body

  const { data, error } = await updateLeaveRequest(id, {
    request_type,
    start_date,
    end_date,
    reason
  })

  if (error) return res.status(500).json({ error })
  return res.status(200).json({ data })
}