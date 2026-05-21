import { supabase } from '../config/supabase.js'

export async function insertLeaveRequest(
  profileId: string,
  leaveData: {
    request_type: string
    start_date: string
    end_date: string
    reason: string
  }){
    console.log('profileId received:', profileId)
    const{data,error} =await supabase
    .from('leave_requests')
    .insert({
      profile_id:profileId,
      request_type:leaveData.request_type,
      start_date:leaveData.start_date,
      end_date:leaveData.end_date,
      reason:leaveData.reason,
    })
    .select()
    .single()
    return {data,error}
  }

export async function fetchCalendar(
    profileId:string,
    role:string,
    from?:string,
    to?:string
){
    let query=supabase.from('leave_requests')
    .select(`id,request_type,start_date,end_date,reason,status,profile_id,profiles:profile_id(first_name,last_name)`)
    .order('start_date',{ascending:true})

    if (role !== 'admin') {
    query = query.eq('profile_id', profileId)
}


if (from) query = query.gte('start_date', from)
  if (to)   query = query.lte('end_date', to)

  const { data, error } = await query
  return { data, error }
}


export async function fetchAttendanceRecords(
  profileId: string,  
  role: string,    
  filters: {
    status?: string
    type?: string
    page: number
    pageSize: number
  }
) {
  const offset = (filters.page - 1) * filters.pageSize

  let query = supabase
    .from('leave_requests')
    .select(`
  id, request_type, start_date, end_date, reason,
  status, created_at, updated_at, profile_id,
  profiles:profile_id(first_name, last_name, email)
`, { count: 'exact' })
    .order('start_date', { ascending: false })
    .range(offset, offset + filters.pageSize - 1)

  if (role !== 'admin') query = query.eq('profile_id', profileId)
  if (filters.status)  query = query.eq('status', filters.status)
  if (filters.type)    query = query.eq('request_type', filters.type)

  const { data, error, count } = await query
  return { data, error, count }
}

export async function findLeaveById(id: string) {
  const { data, error } = await supabase
    .from('leave_requests')
    .select('id')
    .eq('id', id)
    .single()

  return { data, error }
}

export async function findActiveProfile(profileId: string) {
  const { data, error } = await supabase
    .from('profiles')
    .select('id, is_active')
    .eq('id', profileId)
    .eq('is_active', true)
    .single()

  return { data, error }
}

export async function updateLeaveRequestStatus(
  id: string,
  status: string
) {
  const { data, error } = await supabase
    .from('leave_requests')
    .update({ status })
    .eq('id', id)
    .select()
    .single()

  return { data, error }
}

//update leave body
export async function updateLeaveRequest(
  id: string,
  updates: {
    request_type?: string
    start_date?: string
    end_date?: string
    reason?: string
  }
) {
  const { data, error } = await supabase
    .from('leave_requests')
    .update(updates)
    .eq('id', id)
    .select()
    .single()

  return { data, error }
}