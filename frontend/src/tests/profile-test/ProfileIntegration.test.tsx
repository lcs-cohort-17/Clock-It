import { describe, it, expect, beforeEach, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import UserProfilePage from "../../pages/profile";

import type { UserProfileData }
from "../../components/profile/UserProfileData";

describe('Profile Page Integration', () => {
  const mockUser: UserProfileData = {
    fullName: 'Sarah Mthembu',
    email: 'sarah@clockit.app',
    employeeId: 'S-101',
    role: 'Staff',
  }

  beforeEach(() => {
    localStorage.clear()
    vi.clearAllMocks()
  })

  it('should complete full profile edit flow', async () => {
    const user = userEvent.setup()
    render(<UserProfilePage user={mockUser} />)

    await user.click(screen.getByText('Edit profile'))

    const nameInput = screen.getByDisplayValue('Sarah Mthembu')
    await user.clear(nameInput)
    await user.type(nameInput, 'Sarah Nkosi')

    await user.click(screen.getByText('Save'))

    expect(screen.getByText('Sarah Nkosi')).toBeInTheDocument()
  })

  it('should complete clear cache flow', async () => {
    localStorage.setItem('test-data', 'something')
    const user = userEvent.setup()
    render(<UserProfilePage user={mockUser} />)

    await user.click(screen.getByText('Clear local cache'))
    expect(screen.getByText('Clear local cache?')).toBeInTheDocument()

    await user.click(screen.getByText('Yes, clear cache'))
    expect(screen.queryByText('Clear local cache?')).not.toBeInTheDocument()
  })

  it('should complete contact admin flow', async () => {
    const user = userEvent.setup()
    render(<UserProfilePage user={mockUser} />)

    await user.click(screen.getByText('Contact admin'))
    expect(window.location.href).toContain('mailto:admin@clock-it.com')
  })
})