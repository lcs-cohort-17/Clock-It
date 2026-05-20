import { supabase } from '../config/supabase.js'
import type { ApiResponse } from '../types/index.js'
import type { Profile } from '../types/profileInterface.js'

export const getProfilesDb = async (): Promise<ApiResponse<Profile[]>> => {

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
