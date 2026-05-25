import { describe, it, expect, beforeEach, vi } from 'vitest'
import { render, screen, waitForElementToBeRemoved } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import UserProfilePage from "../../pages/profile";

import type { UserProfileData }
from "../../components/profile/UserProfileData";

describe('Profile Page Integration', () => {
  const openSpy = vi
    .spyOn(window, 'open')
    .mockImplementation(() => null)

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

    expect(
      screen.getByRole('heading', { name: 'Sarah Nkosi' })
    ).toBeInTheDocument()
  })

  it('should complete clear cache flow', async () => {
    localStorage.setItem('test-data', 'something')
    const user = userEvent.setup()
    render(<UserProfilePage user={mockUser} />)

    await user.click(screen.getByText('Clear local cache'))
    expect(screen.getByText('Clear local cache?')).toBeInTheDocument()

    await user.click(screen.getByText('Yes, clear cache'))
    await waitForElementToBeRemoved(() =>
      screen.queryByText('Clear local cache?')
    )
  })

  it('should complete contact admin flow', async () => {
    const user = userEvent.setup()
    render(<UserProfilePage user={mockUser} />)

    await user.click(screen.getByText('Contact admin'))
    expect(openSpy).toHaveBeenCalledWith(
      'mailto:admin@clock-it.com?subject=Clock-It Support Request'
    )
  })
})
