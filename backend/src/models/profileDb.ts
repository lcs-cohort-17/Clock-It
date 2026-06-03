import { supabase } from '../config/supabase.js'
import type { ApiResponse } from '../types/index.js'
import type { Profile } from '../types/profileInterface.js'
import { generatePassword } from '../utils/generatePassword.js'
import bcrypt from 'bcrypt'

<<<<<<< HEAD
export const getProfilesDb = async (): Promise<ApiResponse<Profile[]>> => {
=======
export const adminGettingAllUsersDb = async (): Promise<ApiResponse<Profile[]>> => {
>>>>>>> origin/SizaMpafa/SM-team/testing

  const { data, error } = await supabase
    .from('profiles')
    .select('*')

  if (error) return { success: false, error: error.message }

  return { success: true, data }
}

//ZAHRAA'S CODE
function capitalizeFirstName(name: string | null): string | null {
  if (!name) return name
  return name.charAt(0).toUpperCase() + name.slice(1).toLowerCase()
}

export async function getProfileByIdDb(employeeId: string): Promise<ApiResponse<Profile>> {
    try {
    const { data, error } = await supabase
      .from('profiles')
      .select('*')
      .eq('employee_id', employeeId)  
      .single()
    
    if (error) {
      return { success: false, error: error.message }
    }
    
    if (data && data.first_name) {
      data.first_name = capitalizeFirstName(data.first_name)
    }
    
    return { success: true, data }
  } catch (error) {
    return {
      success: false,
      error: error instanceof Error ? error.message : 'Unknown error'
    }
  }
}
//END OF ZAHRAA'S CODE

// ─── CREATE ──────────────────────────────────────────────────
<<<<<<< HEAD
export const createProfileDb = async ( 
=======
export const adminCreatingUserDb = async ( 
>>>>>>> origin/SizaMpafa/SM-team/testing
    first_name: string, 
    last_name: string, 
    employee_id: string, 
    role: 'staff' | 'admin', 
    email: string): Promise<ApiResponse<Profile>> => {
        if (role !== 'staff' && role !== 'admin') {
            return { success: false, error: 'Role must be either staff or admin' }
        }  
        if (role === 'staff' && !employee_id.startsWith('S-')) 
            {    return { success: false, error: 'Staff employee_id must start with S-' }  
        }  
        if (role === 'admin' && !employee_id.startsWith('A-')) {    
            return { success: false, error: 'Admin employee_id must start with A-' }  
        }  // Generate plain text password  
        const plainPassword = generatePassword()  // Hash it before storing  
        const hashedPassword = await bcrypt.hash(plainPassword, 10)  
        // Console log so you can see both  
        console.log('─────────────────────────────')  
        console.log('Plain text password:', plainPassword)  
        console.log('Hashed password:', hashedPassword)  
        console.log('─────────────────────────────')  
        const { data, error } = await supabase
        .from('profiles')
        .insert({ first_name, 
            last_name, 
            employee_id, 
            role, 
            email, 
            password: hashedPassword,  // store hash in DB 
            is_active: true    
            })
            .select()
            .single()
            if (error) return { success: false, error: error.message }  // Override password in response — return plain text to admin  
            // // Admin needs to see it to share with staff  
            return { success: true, 
                data: {
                    ...data, 
                    password: plainPassword // plain text in response, not the hash    
                    }  
                }
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
        return { success: true, data }
}

<<<<<<< HEAD
// export async function createProfileDb() {}
// ─── UPDATE ──────────────────────────────────────────────────
export const updateProfileDb = async (
=======
// export async function adminCreatingUserDb() {}
// ─── UPDATE ──────────────────────────────────────────────────
export const adminUpdatingUserDb = async (
>>>>>>> origin/SizaMpafa/SM-team/testing
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
<<<<<<< HEAD
export const deleteProfileDb = async (
=======
export const adminDeletingUserDb = async (
>>>>>>> origin/SizaMpafa/SM-team/testing
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

// ─── RESET PASSWORD (Admin) — ticket 031 ─────────────────────
// Admin resets for staff — generates random, plain text, no hashexport 
export const resetPasswordDb = async ( employee_id: string): Promise<ApiResponse<Profile>> => {  
    const newPassword = generatePassword()  // plain text — returned to admin  
    const { data, error } = await supabase
    .from('profiles')
    .update({ password: newPassword })
    .eq('employee_id', employee_id)
    .select()
    .single()
    if (error) return { success: false, error: error.message }
    return { success: true, data }
}

// ─── UPDATE OWN PASSWORD (Staff) — ticket 025 ────────────────
// // Staff changes their OWN password — they type it, it gets hashed
export const updatePasswordDb = async ( 
  employee_id: string, 
  hashedPassword: string  // controller hashes it before calling this
  ): Promise<ApiResponse<Profile>> => {
    const { data, error } = await supabase
    .from('profiles')
    .update({ password: hashedPassword })
    .eq('employee_id', employee_id)
    .select()
    .single()
    if (error) return { success: false, error: error.message }
    return { success: true, data }
  }