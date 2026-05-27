import type { User } from '../types/auth'

export const mockUsers: User[] = [
  {
    id: '1',
    name: 'Taaraa Admin',
    email: 'taaraa@clockit.com',
    password: 'admin123',
    role: 'admin',
    employeeId: 'EMP004',
    microsoftEmail: 'taaraa@clockit.com',
  },
  {
    id: '2',
    name: 'Shaheed Staff',
    email: 'shaheed@clockit.com',
    password: 'staff123',
    role: 'staff',
    employeeId: 'EMP001',
    microsoftEmail: 'shaheed@clockit.com',
  },
]
