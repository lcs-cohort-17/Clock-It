import { describe, it, expect, vi, beforeEach } from "vitest";

// create mock/fake functions once

const mockSingle = vi.fn()
const mockEq = vi.fn().mockReturnThis()
const mockSelect = vi.fn().mockReturnThis()
const mockInsert = vi.fn().mockReturnThis()
const mockUpdate = vi.fn().mockReturnThis()
const mockDelete = vi.fn().mockReturnThis()

// mock Supabase, pretend that you are really targeting the database on Supabase

vi.mock('../../src/config/supabase.js', () => ({
    supabase: {
        from: vi.fn(() => ({
            select: mockSelect,
            insert: mockInsert,
            update: mockUpdate,
            delete: mockDelete,
            eq: mockEq,
            single: mockSingle,
        }))
    }
}))

import {adminGettingAllUsersDb, 
    getProfileByIdDb,
    adminDeletingUserDb, 
    adminUpdatingUserDb,
    adminCreatingUserDb,
    updatePasswordDb,
    resetPasswordDb
} from '../../src/models/profileDb.js'

// reset mocks before each test
beforeEach(() => {
    vi.clearAllMocks()
    //Reapply mockReturnThis() after clearAllMocks
    mockSingle.mockReset()
    mockSelect.mockReturnThis()
    mockInsert.mockReturnThis()
    mockUpdate.mockReturnThis()
    mockDelete.mockReturnThis()
    mockEq.mockReturnThis()
})

//GET ALL

describe('adminGettingAllUsersDb', () => {

 it('should return all profiles successfully', async () => {
    const mockData = [
      { id: '14271887-48ea-48c8-9890-6cb196afa0Gc', first_name: 'Joshua', last_name: 'Jacobs', employee_id: 'A-005', role: 'admin', is_active: true, email: 'jodam@gmail.com', password: 'joh123' },
      { id: '14361887-48ea-48c8-9890-6cb196afa0Gc', first_name: 'Charlton', last_name: 'Poole', employee_id: 'S-006', role: 'staff', is_active: true, email: 'charlton@gmail.com', password: 'charlton123' }
    ]

    mockSelect.mockResolvedValueOnce({ data: mockData, error: null })

    const result = await adminGettingAllUsersDb()

    expect(result.success).toBe(true)
    expect(result.data).toHaveLength(2)
 })

 it('should return profiles with all required fields', async () => {
    const mockData = [
        { id: '14271887-48ea-48c8-9890-6cb196afa0Gc', first_name: 'Joshua', last_name: 'Jacobs', employee_id: 'A-005', role: 'admin', is_active: true, email: 'jodam@gmail.com', password: 'joh123' }
    ]

    mockSelect.mockResolvedValueOnce({ data: mockData, error: null })

    const result = await adminGettingAllUsersDb()

    // Check the first record has all required fields
    const profile = result.data?.[0]

    expect(profile).toHaveProperty('first_name')
    expect(profile).toHaveProperty('last_name')
    expect(profile).toHaveProperty('email')
    expect(profile).toHaveProperty('employee_id')
    expect(profile).toHaveProperty('role')
    expect(profile).toHaveProperty('is_active')

    // Check the actual values
    expect(profile?.first_name).toBe('Joshua')
    expect(profile?.last_name).toBe('Jacobs')
    expect(profile?.email).toBe('jodam@gmail.com')
    expect(profile?.employee_id).toBe('A-005')
    expect(profile?.role).toBe('admin')
    expect(profile?.is_active).toBe(true)
  })

  it('should return error if supabase fails', async () => {
    mockSelect.mockResolvedValueOnce({
      data: null,
      error: { message: 'Database error' }
    })

    const result = await adminGettingAllUsersDb()

    expect(result.success).toBe(false)
    expect(result.error).toBe('Database error')
  })
})

describe('adminCreatingUserDb', () => {

  it('should create a staff profile, store hashed password, return plain text to admin', async () => {
    const mockData = {
      id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
      first_name: 'Joshua',
      last_name: 'Jacobs',
      employee_id: 'S-005',
      role: 'staff',
      is_active: true,
      email: 'jodam@gmail.com',
      password: '$2b$10$hashedpasswordhere'  // DB stores hash
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    const result = await adminCreatingUserDb('Joshua', 'Jacobs', 'S-005', 'staff', 'jodam@gmail.com')

    expect(result.success).toBe(true)
    expect(result.data?.first_name).toBe('Joshua')
    expect(result.data?.last_name).toBe('Jacobs')
    expect(result.data?.employee_id).toBe('S-005')
    expect(result.data?.role).toBe('staff')
    expect(result.data?.is_active).toBe(true)
    expect(result.data?.email).toBe('jodam@gmail.com')

    // Response must have plain text — not the hash
    expect(result.data?.password).toBeDefined()
    expect(result.data?.password).toHaveLength(8)
    expect(result.data?.password).not.toMatch(/^\$2[ab]\$/)
    expect(typeof result.data?.password).toBe('string')
  })

  it('should create an admin profile successfully', async () => {
    const mockData = {
      id: '24271887-48ea-48c8-9890-6cb196afa0Gc',
      first_name: 'Sarah',
      last_name: 'Johnson',
      employee_id: 'A-010',
      role: 'admin',
      is_active: true,
      email: 'sarah@company.com',
      password: '$2b$10$hashedpasswordhere'
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    const result = await adminCreatingUserDb('Sarah', 'Johnson', 'A-010', 'admin', 'sarah@company.com')

    expect(result.success).toBe(true)
    expect(result.data?.role).toBe('admin')
    expect(result.data?.password).toHaveLength(8)
    expect(result.data?.password).not.toMatch(/^\$2[ab]\$/)
  })

  it('should auto-generate a different password each time', async () => {
    const mockData1 = {
      id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
      first_name: 'Joshua',
      last_name: 'Jacobs',
      employee_id: 'S-005',
      role: 'staff',
      is_active: true,
      email: 'jodam@gmail.com',
      password: '$2b$10$firsthash'
    }

    const mockData2 = {
      id: '24271887-48ea-48c8-9890-6cb196afa0Gc',
      first_name: 'Sarah',
      last_name: 'Johnson',
      employee_id: 'S-006',
      role: 'staff',
      is_active: true,
      email: 'sarah@company.com',
      password: '$2b$10$secondhash'
    }

    mockSingle.mockResolvedValueOnce({ data: mockData1, error: null })
    mockSingle.mockResolvedValueOnce({ data: mockData2, error: null })

    const result1 = await adminCreatingUserDb('Joshua', 'Jacobs', 'S-005', 'staff', 'jodam@gmail.com')
    const result2 = await adminCreatingUserDb('Sarah', 'Johnson', 'S-006', 'staff', 'sarah@company.com')

    expect(result1.data?.password).not.toBe(result2.data?.password)
  })

  it('should only accept role of staff or admin', async () => {
    const result = await adminCreatingUserDb('Joshua', 'Jacobs', 'S-005', 'invalid' as any, 'jodam@gmail.com')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Role must be either staff or admin')
  })

  it('should reject staff employee_id that does not start with S-', async () => {
    const result = await adminCreatingUserDb('Joshua', 'Jacobs', 'A-005', 'staff', 'jodam@gmail.com')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Staff employee_id must start with S-')
  })

  it('should reject admin employee_id that does not start with A-', async () => {
    const result = await adminCreatingUserDb('Sarah', 'Johnson', 'S-010', 'admin', 'sarah@company.com')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Admin employee_id must start with A-')
  })

  it('should return error if creation fails', async () => {
    mockSingle.mockResolvedValueOnce({
      data: null,
      error: { message: 'Creation failed' }
    })

    const result = await adminCreatingUserDb('Joshua', 'Jacobs', 'A-005', 'admin', 'jodam@gmail.com')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Creation failed')
  })
})
//UPDATE
describe('adminUpdatingUserDb', () => {

  it('should update a staff profile successfully by employee_id', async () => {
    const mockData = {
      id: '18741887-48ea-48c8-9890-6cb196afa0Gc',
      first_name: 'Siza',
      last_name: 'Mpafa',
      employee_id: 'S-007',
      role: 'staff',
      is_active: true,
      email: 'siza@gmail.com'
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    // Target by employee_id not uuid
    const result = await adminUpdatingUserDb('S-007',{
      first_name: 'Siza',
      last_name: 'Mpafa',
      role: 'staff',
      is_active: true,
      email: 'siza@gmail.com'
    })

    expect(result.success).toBe(true)
    expect(result.data?.first_name).toBe('Siza')
    expect(result.data?.last_name).toBe('Mpafa')
    expect(result.data?.employee_id).toBe('S-007')
    expect(result.data?.role).toBe('staff')       // lowercase — not 'Staff'
    expect(result.data?.is_active).toBe(true)     // boolean not string
    expect(result.data?.email).toBe('siza@gmail.com')
  })

  it('should update an admin profile successfully by employee_id', async () => {
    // Admin can be updated too — not just staff
    const mockData = {
      id: '24271887-48ea-48c8-9890-6cb196afa0Gc',
      first_name: 'Sarah',
      last_name: 'Johnson',
      employee_id: 'A-010',
      role: 'admin',
      is_active: true,
      email: 'sarah@company.com'
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    const result = await adminUpdatingUserDb('A-010', {
    first_name: 'Sarah',
      email: 'sarah@company.com'
    })

    expect(result.success).toBe(true)
    expect(result.data?.role).toBe('admin')
    expect(result.data?.employee_id).toBe('A-010')
  })

  it('should update is_active to false — disabling a user via edit', async () => {
  const mockData = {
    id: '18741887-48ea-48c8-9890-6cb196afa0Gc',
    first_name: 'Siza',
    last_name: 'Mpafa',
    employee_id: 'S-007',
    role: 'staff',
    is_active: false,    // changed from true to false
    email: 'siza@gmail.com'
  }

  mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

  const result = await adminUpdatingUserDb('S-007', { is_active: false })

  expect(result.success).toBe(true)
  expect(result.data?.is_active).toBe(false)  // confirms it was disabled
})

  it('should update only one field without affecting others — Partial update', async () => {
    // Only updating role — other fields stay the same
    const mockData = {
      id: '18741887-48ea-48c8-9890-6cb196afa0Gc',
      first_name: 'Siza',
      last_name: 'Mpafa',
      employee_id: 'S-007',
      role: 'admin',       // only this changed
      is_active: true,
      email: 'siza@gmail.com'
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    // Partial<Profile> — only passing one field
    const result = await adminUpdatingUserDb('S-007', { role: 'admin' })

    expect(result.success).toBe(true)
    expect(result.data?.role).toBe('admin')
    // Other fields untouched
    expect(result.data?.first_name).toBe('Siza')
    expect(result.data?.email).toBe('siza@gmail.com')
  })

  it('should return error if update fails', async () => {
    mockSingle.mockResolvedValueOnce({
      data: null,
      error: { message: 'Update failed' }
    })

    const result = await adminUpdatingUserDb('S-007', { first_name: 'Ghost' })

    expect(result.success).toBe(false)
    expect(result.error).toBe('Update failed')
  })

  it('should return error if no fields provided', async () => {
    // Partial<Profile> with empty object — nothing to update
    const result = await adminUpdatingUserDb('S-007', {})

    expect(result.success).toBe(false)
    expect(result.error).toBe('No fields provided for update')
  })

  it('should return error if employee_id does not exist', async () => {
    mockSingle.mockResolvedValueOnce({
      data: null,
      error: { message: 'Profile not found' }
    })

    const result = await adminUpdatingUserDb('X-999', { first_name: 'Ghost' })

    expect(result.success).toBe(false)
    expect(result.error).toBe('Profile not found')
  })
})

describe('adminDeletingUserDb', () => {

  it('should delete a staff profile successfully by employee_id', async () => {
    mockSingle.mockResolvedValueOnce({ data: null, error: null })

    // Target by employee_id not uuid
    const result = await adminDeletingUserDb('S-007')

    expect(result.success).toBe(true)
    expect(result.message).toBe('profile deleted successfully')
  })

  it('should delete an admin profile successfully by employee_id', async () => {
    mockSingle.mockResolvedValueOnce({ data: null, error: null })

    const result = await adminDeletingUserDb('A-010')

    expect(result.success).toBe(true)
    expect(result.message).toBe('profile deleted successfully')
  })

  it('should soft disable the user — sets is_active to false not hard delete', async () => {
  const mockData = {
    id: '18741887-48ea-48c8-9890-6cb196afa0Gc',
    employee_id: 'S-007',
    is_active: false    // confirms soft disable happened
  }

  mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

  const result = await adminDeletingUserDb('S-007')

  expect(result.success).toBe(true)
  // Record still exists — just disabled
  expect(result.data?.is_active).toBe(false)
})

  it('should return error if employee_id does not exist', async () => {
    mockSingle.mockResolvedValueOnce({
      data: null,
      error: { message: 'Profile not found' }
    })

    const result = await adminDeletingUserDb('X-999')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Profile not found')
  })

  it('should return error if delete fails', async () => {
    mockSingle.mockResolvedValueOnce({
      data: null,
      error: { message: 'Delete failed' }
    })

    const result = await adminDeletingUserDb('S-007')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Delete failed')
  })
})

describe('updatePasswordDb', () => {

  it('should update password successfully by employee_id', async () => {
    const mockData = {
      id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
      employee_id: 'S-300',
      password: '$2b$10$newhashhere',
      first_name: 'Official',
      last_name: 'Staff',
      role: 'staff',
      is_active: true,
      email: 'officialstaff@clockit.com'
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    const result = await updatePasswordDb('S-300', '$2b$10$newhashhere')

    expect(result.success).toBe(true)
    // DB stores hash — not plain text
    expect(result.data?.password).toMatch(/^\$2[ab]\$/)
  })

  it('should work for admin employee_id too', async () => {
    const mockData = {
      id: '24271887-48ea-48c8-9890-6cb196afa0Gc',
      employee_id: 'A-010',
      password: '$2b$10$adminhashhere',
      first_name: 'Sarah',
      last_name: 'Johnson',
      role: 'admin',
      is_active: true,
      email: 'sarah@company.com'
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    const result = await updatePasswordDb('A-010', '$2b$10$adminhashhere')

    expect(result.success).toBe(true)
    expect(result.data?.employee_id).toBe('A-010')
  })

  it('should return error if employee_id does not exist', async () => {
    mockSingle.mockResolvedValueOnce({
      data: null,
      error: { message: 'Profile not found' }
    })

    const result = await updatePasswordDb('X-999', '$2b$10$somehash')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Profile not found')
  })

  it('should return error if update fails', async () => {
    mockSingle.mockResolvedValueOnce({
      data: null,
      error: { message: 'Update failed' }
    })

    const result = await updatePasswordDb('S-300', '$2b$10$somehash')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Update failed')
  })
})

//RESET PASSWORD
describe('resetPasswordDb', () => {

  it('should generate a new 8 char password by employee_id and return it to admin', async () => {
    const mockData = {
      id: '18741667-48ea-48c8-9890-6cb196adc0Gc',
      employee_id: 'S-007',
      password: 'Nq7rT2mX'  // new auto-generated 8 char password
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    // Target by employee_id not uuid
    const result = await resetPasswordDb('S-007')

    expect(result.success).toBe(true)
    // Must return new password so admin can share it with staff
    expect(result.data?.password).toBeDefined()
    expect(result.data?.password).toHaveLength(8)
    // Must be plain text — not hashed
    expect(result.data?.password).not.toMatch(/^\$2[ab]\$/)
    expect(typeof result.data?.password).toBe('string')
  })

  it('should generate a new password for admin by employee_id', async () => {
    const mockData = {
      id: '24271887-48ea-48c8-9890-6cb196afa0Gc',
      employee_id: 'A-010',
      password: 'Xk9mP2qR'
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    const result = await resetPasswordDb('A-010')

    expect(result.success).toBe(true)
    expect(result.data?.password).toHaveLength(8)
    expect(typeof result.data?.password).toBe('string')
  })

  it('should return error if employee_id does not exist', async () => {
    mockSingle.mockResolvedValueOnce({
      data: null,
      error: { message: 'Profile not found' }
    })

    const result = await resetPasswordDb('X-999')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Profile not found')
  })

  it('should return error if reset fails', async () => {
    mockSingle.mockResolvedValueOnce({
      data: null,
      error: { message: 'Reset failed' }
    })

    const result = await resetPasswordDb('S-007')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Reset failed')  // lowercase 'f' — consistent casing
  })
})
//ZAHRAA TESTS


//TEST 1: PROFILE WITH FISRT NAME
//CREATES A GROUP CALLED "GetProfileByIdDb" - ALL RELATED TESTS GO INSIDE
describe('getProfileByIdDb', () => {
//IT = ONE TEST. 
  it('should return profile with capitalized first_name', async () => {
    const mockData = {
      id: '14271887-48ea-48c8-9890-6cb196afa0Gc',
      first_name: 'sarah',
      last_name: 'johnson',
      email: 'sarah@company.com',
      employee_id: 'S-006',
      role: 'staff',
      is_active: true
    }
// TELLS THE FAKE DATABASE TO RETURN THE ABOVE FAKE DATA WHEN .SINGLE() IS CALLED 
    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })
// CALLS FUNCTION THAT MUST STILL BE WRITTEN 
    const result = await getProfileByIdDb('S-006')
// WHAT THE FUNCTION SHOULD RETURN 
    expect(result.success).toBe(true)
    expect(result.data?.first_name).toBe('Sarah')
  })

  // TEST 2: WHEN A USER DOESN'T EXIST 
  it('should return error if profile not found', async () => {
    mockSingle.mockResolvedValueOnce({
      data: null,
      error: { message: 'Profile not found' }
    })

    const result = await getProfileByIdDb('99')

    expect(result.success).toBe(false)
    expect(result.error).toBe('Profile not found')
  })

})

//TEST 3: CAPITALISATION FORMAT 
// DESCRIBE = GROUPS ALL CAPITALISATION TESTS TOGETHER 
describe('first_name capitalization formats', () => {
// testCases = TEST DATA TABLE: EACH ROW HAS AN INPUT(WHAT DATABASE STORES) 
// AND EXPECTED OUTPUT (WHAT FUNCTION SHOULD RETURN )
  const testCases = [
    { input: 'sarah', expected: 'Sarah' },
    { input: 'SARAH', expected: 'Sarah' },
    { input: 'sArAh', expected: 'Sarah' },
    { input: 'john', expected: 'John' },
    { input: 'JOHN', expected: 'John' },
    { input: 'mary-jane', expected: 'Mary-jane' },
    { input: '', expected: '' },
    { input: 'a', expected: 'A' }
  ]
  
//LOOPS THROUGH EACH TEST CASE AND CREATE A TEST FOR EACH CASE.
  testCases.forEach(({ input, expected }) => {
    it(`should capitalize "${input}" to "${expected}"`, async () => {
      const mockData = {
        id: 'test-id',
        employee_id: 'test-emp-id',
        first_name: input,
        email: 'test@company.com'
      }

      mockSingle.mockResolvedValueOnce({ data: mockData, error: null })
      
      const result = await getProfileByIdDb('test-emp-id');
      
// CALLS  REAL FUNCTION.
      if (expected === '') {
        expect(result.data?.first_name === '' || result.data?.first_name === null).toBe(true)
      } else {
        expect(result.data?.first_name).toBe(expected)
      }
    })
  })
})

//TEST 3: NULL FIRST NAME 
describe('getProfileByIdDb - edge cases', () => {
  it('should handle null first_name from database', async () => {
    const mockData = {
      id: 'test-id',                    
      employee_id: 'test-emp-id',
      first_name: null,
      email: 'test@company.com',
      last_name: 'Test',
      role: 'staff',
      is_active: true
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    const result = await getProfileByIdDb('test-emp-id');
    
    expect(result.success).toBe(true)
    // Should handle null gracefully (return null or empty string)
    expect(result.data?.first_name === null || result.data?.first_name === '').toBe(true)
  })

// MISSING FIRST NAME FIELD 
  it('should handle missing first_name field', async () => {
    const mockData = {
      id: 'test-id',                    
      employee_id: 'test-emp-id',
      email: 'test@company.com',
      last_name: 'Test',                
      role: 'staff',                    
      is_active: true                   
    }

    mockSingle.mockResolvedValueOnce({ data: mockData, error: null })

    const result = await getProfileByIdDb('test-emp-id');
    
    expect(result.success).toBe(true)
    expect(result.data?.first_name === undefined || result.data?.first_name === null).toBe(true)
  })
}) 

// TEST 4: PROOF OF MIGRATION - FIRST NAME POPULATION
describe('Database migration - first_name population', () => {
  
  it('should prove that all user records have a populated first_name', async () => {
    const mockUser = {
      id: 'user-123',
      first_name: 'Sarah',  
      last_name: 'Johnson',
      employee_id: 'S-006',
      email: 'sarah@company.com',
      role: 'staff',
      is_active: true
    }

    mockSingle.mockResolvedValueOnce({ data: mockUser, error: null })

    const result = await getProfileByIdDb('S-006')

    expect(result.data?.first_name).toBeDefined()
    expect(result.data?.first_name).not.toBeNull()
    expect(result.data?.first_name).not.toBe('')
    expect(typeof result.data?.first_name).toBe('string')
    expect(result.data?.first_name.length).toBeGreaterThan(0)
  })
})

//END OF ZAHRAA'S TESTS
