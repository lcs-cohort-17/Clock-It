import { describe, it, expect, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import UserProfilePage from "../../pages/profile";

import type { UserProfileData }
from "../../components/profile/UserProfileData";

// Mock child components
vi.mock('../../components/profile/Profile_Workspace', () => ({
  Profile_Workspace: ({ user }: { user: UserProfileData }) => (
    <div data-testid="profile-workspace">
      Mock Profile: {user.fullName}
    </div>
  ),
}))

vi.mock('../../components/profile/Support_Workspace', () => ({
  Support_Workspace: () => (
    <div data-testid="support-workspace">Mock Support</div>
  ),
}))

describe('UserProfilePage', () => {
  const mockUser: UserProfileData = {
    fullName: 'Sarah Mthembu',
    email: 'sarah@clockit.app',
    employeeId: 'S-101',
    role: 'Staff',
  }

  it('should render page header correctly', () => {
    render(<UserProfilePage user={mockUser} />)
    
    expect(screen.getByText('Profile')).toBeInTheDocument()
    expect(screen.getByText('User Profile')).toBeInTheDocument()
    expect(screen.getByText('Your account information')).toBeInTheDocument()
  })

  it('should render profile workspace', () => {
    render(<UserProfilePage user={mockUser} />)
    expect(screen.getByTestId('profile-workspace')).toBeInTheDocument()
    expect(screen.getByText(/Mock Profile: Sarah Mthembu/)).toBeInTheDocument()
  })

  it('should render support workspace', () => {
    render(<UserProfilePage user={mockUser} />)
    expect(screen.getByTestId('support-workspace')).toBeInTheDocument()
  })

  it('should render password section placeholder', () => {
    render(<UserProfilePage user={mockUser} />)
    expect(screen.getByText('Password')).toBeInTheDocument()
  })
})