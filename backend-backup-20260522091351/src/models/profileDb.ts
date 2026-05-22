import { supabase } from '../config/supabase.js'

type DbResult<T> = {
  success: boolean
  data: T | null
  error: string | null
}

const generatePassword = () => {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
  let password = ''
  for (let i = 0; i < 8; i++) {
    password += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  return password
}

export async function getProfilesDb() {
  const { data, error } = await supabase.from('profiles').select('*')
  if (error) {
    return { success: false, data: null, error: error.message }
  }
  return { success: true, data, error: null }
}

export async function createProfileDb(first_name: string, last_name: string, employee_id: string, role: string, email: string) {
  const password = generatePassword()
  const payload = {
    first_name,
    last_name,
    employee_id,
    role,
    email,
    password,
    is_active: true
  }

  const { data, error } = await supabase.from('profiles').insert(payload).select().single()
  if (error) {
    return { success: false, data: null, error: error.message }
  }

  return { success: true, data, error: null }
}

export async function updateProfileDb(id: string, updates: Record<string, unknown>) {
  const { data, error } = await supabase.from('profiles').update(updates).eq('id', id).select().single()
  if (error) {
    return { success: false, data: null, error: error.message }
  }
  return { success: true, data, error: null }
}

export async function deleteProfileDb(id: string) {
  const { error } = await supabase.from('profiles').delete().eq('id', id)
  if (error) {
    return { success: false, data: null, error: error.message }
  }
  return { success: true, data: null, error: null }
}

export async function resetPasswordDb(id: string) {
  const password = generatePassword()
  const { data, error } = await supabase.from('profiles').update({ password }).eq('id', id).select().single()
  if (error) {
    return { success: false, data: null, error: error.message }
  }
  return { success: true, data, error: null }
}
