import { supabase } from '../config/supabase.js'
import type { ApiResponse } from '../types/index.js'
import type { Profile } from '../types/profileInterface.js'
import { generatePassword } from '../utils/generatePassword.js'

export const getProfilesDb = async (): Promise<ApiResponse<Profile[]>> => {

  const { data, error } = await supabase
    .from('profiles')
    .select('*')

  if (error) return { success: false, error: error.message }

  return { success: true, data }
}

// ─── CREATE ──────────────────────────────────────────────────
export const createProfileDb = async ( 
        first_name: string, 
        last_name: string, 
        employee_id: string, 
        role: 'staff' | 'admin', 
        email: string): Promise<ApiResponse<Profile>> => {
            if (role !== 'staff' && role !== 'admin') {
                return { success: false, error: 'Role must be either staff or admin' } 
            }
            
            if (role === 'staff' && !employee_id.startsWith('S-')) {
                return { success: false, error: 'Staff employee_id must start with S-' }
            }
            if (role === 'admin' && !employee_id.startsWith('A-')) {
                return { success: false, error: 'Admin employee_id must start with A-' }
            }  
            // Auto generate 8 char plain text password — no hashing here  // Controller will hash before passing to DB for user-set passwords  // Admin-generated passwords stay plain text per ticket 031  
            const password = generatePassword()
            const { data, error } = await supabase 
            .from('profiles') 
            .insert({ first_name, last_name, employee_id, role, email, password, is_active: true})
            .select()
            .single()
            if (error) return { success: false, error: error.message }  
            return { success: true, data }
}
// // ─── LOGIN ───────────────────────────────────────────────────
export const loginProfileDb = async ( email: string): Promise<ApiResponse<Profile>> => {
     const { data, error } = await supabase
      .from('profiles')
      .select('*')    .eq('email', email)
      .single()
      if (error) return { success: false, error: error.message }
      // Disabled accounts cannot login  
      if (!data.is_active) {
        return { success: false, error: 'Account is disabled' }
        }
        return { success: true, data }}

// export async function createProfileDb() {}
// ─── UPDATE ──────────────────────────────────────────────────
export const updateProfileDb = async (
  employee_id: string,
  updates: Partial<Profile>
): Promise<ApiResponse<Profile>> => {

  // Prevent empty update — drives the 'no fields provided' test
  if (Object.keys(updates).length === 0) {
    return { success: false, error: 'No fields provided for update' }
  }

  const { data, error } = await supabase
    .from('profiles')
    .update(updates)
    .eq('employee_id', employee_id)  // target by employee_id not uuid
    .select()
    .single()

  if (error) return { success: false, error: error.message }

  return { success: true, data }
}

// ─── DELETE (SOFT) ───────────────────────────────────────────
export const deleteProfileDb = async (
  employee_id: string
): Promise<ApiResponse<Profile>> => {

  // Soft delete — sets is_active to false, record stays in DB
  // This drives: 'should soft disable the user' test
  const { data, error } = await supabase
    .from('profiles')
    .update({ is_active: false })
    .eq('employee_id', employee_id)
    .select()
    .single()

  if (error) return { success: false, error: error.message }

  return { success: true, data, message: 'profile deleted successfully' }
}
