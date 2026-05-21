export type RequestTypes = 'leave' | 'sick' | 'annual' | 'unpaid'|'other' 
export type RequestStatus = 'pending' | 'approved' | 'rejected'

export interface LeaveRequest {
    id:string,
    profile_id:string
    request_type:RequestTypes
    start_date:string 
    end_date:string 
    reason:string
    status:RequestStatus
    created_at:string
    updated_at:string
}

//Calender-By Dates
export interface Calendar {
  date: string
  requests: (Pick<LeaveRequest, 'id' | 'profile_id' | 'request_type' | 'status'> & {
    first_name: string
    last_name:string
  })[]
}

// Attendance page — flat list with user details
export interface AttendanceRecord extends LeaveRequest {
  first_name: string
  last_name: string
  email: string
}
