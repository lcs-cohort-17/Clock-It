import { describe, it, expect, beforeEach, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import Profile_Workspace from "../../components/profile/Profile_Workspace";
import type { UserProfileData } from "../../components/profile/UserProfileData";

describe('Profile_Workspace', () => {
  const mockUser: UserProfileData = {
    fullName: 'Sarah Mthembu',
    email: 'sarah@clockit.app',
    employeeId: 'S-101',
    role: 'Staff',
    avatarUrl: 'https://example.com/avatar.jpg',
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('Rendering', () => {
    it('should display user information correctly', () => {
      render(<Profile_Workspace user={mockUser} />)

      expect(screen.getByText('Sarah Mthembu')).toBeInTheDocument()
      expect(screen.getByText('sarah@clockit.app')).toBeInTheDocument()
      expect(screen.getByText('S-101')).toBeInTheDocument()
      expect(screen.getByText('Staff')).toBeInTheDocument()
    })

    it('should display online status badge', () => {
      render(<Profile_Workspace user={mockUser} />)
      expect(screen.getByText('Online')).toBeInTheDocument()
    })

    it('should show initials when no avatar URL is provided', () => {
      const userWithoutAvatar = { ...mockUser, avatarUrl: undefined }
      render(<Profile_Workspace user={userWithoutAvatar} />)
      expect(screen.getByText('SM')).toBeInTheDocument()
    })

    it('should show upload image button', () => {
      render(<Profile_Workspace user={mockUser} />)
      expect(screen.getByText('Upload image')).toBeInTheDocument()
    })

    it('should show edit profile button when not editing', () => {
      render(<Profile_Workspace user={mockUser} />)
      expect(screen.getByText('Edit profile')).toBeInTheDocument()
      expect(screen.queryByText('Save')).not.toBeInTheDocument()
      expect(screen.queryByText('Cancel')).not.toBeInTheDocument()
    })
  })

  describe('Edit Mode', () => {
    it('should enter edit mode when clicking edit button', async () => {
      const user = userEvent.setup()
      render(<Profile_Workspace user={mockUser} />)

      const editButton = screen.getByText('Edit profile')
      await user.click(editButton)

      expect(screen.getByText('Save')).toBeInTheDocument()
      expect(screen.getByText('Cancel')).toBeInTheDocument()
      
      const nameInput = screen.getByDisplayValue('Sarah Mthembu')
      expect(nameInput).toBeInTheDocument()
      expect(nameInput.tagName).toBe('INPUT')
    })

    it('should update input values when typing', async () => {
      const user = userEvent.setup()
      render(<Profile_Workspace user={mockUser} />)

      await user.click(screen.getByText('Edit profile'))

      const nameInput = screen.getByDisplayValue('Sarah Mthembu')
      await user.clear(nameInput)
      await user.type(nameInput, 'Sarah Nkosi')

      expect(nameInput).toHaveValue('Sarah Nkosi')
    })

    it('should cancel editing without saving changes', async () => {
      const user = userEvent.setup()
      render(<Profile_Workspace user={mockUser} />)

      await user.click(screen.getByText('Edit profile'))

      const nameInput = screen.getByDisplayValue('Sarah Mthembu')
      await user.clear(nameInput)
      await user.type(nameInput, 'Sarah Nkosi')

      await user.click(screen.getByText('Cancel'))

      expect(screen.queryByText('Save')).not.toBeInTheDocument()
      expect(screen.getByText('Sarah Mthembu')).toBeInTheDocument()
    })

    it('should save changes when clicking save button', async () => {
      const user = userEvent.setup()
      render(<Profile_Workspace user={mockUser} />)

      await user.click(screen.getByText('Edit profile'))

      const nameInput = screen.getByDisplayValue('Sarah Mthembu')
      await user.clear(nameInput)
      await user.type(nameInput, 'Sarah Nkosi')

      await user.click(screen.getByText('Save'))

      expect(screen.queryByText('Save')).not.toBeInTheDocument()
      expect(screen.getByText('Sarah Nkosi')).toBeInTheDocument()
    })
  })

  describe('Avatar Upload', () => {
    it('should have file input element with correct attributes', () => {
      render(<Profile_Workspace user={mockUser} />)
      
      const fileInput = document.querySelector('input[type="file"]')
      expect(fileInput).toHaveAttribute('accept', 'image/*')
      expect(fileInput).toHaveClass('hidden')
    })

    it('should update avatar preview when image is selected', async () => {
      const user = userEvent.setup()
      render(<Profile_Workspace user={mockUser} />)

      const file = new File(['dummy content'], 'avatar.png', { type: 'image/png' })
      const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement

      await user.upload(fileInput, file)

      expect(globalThis.URL.createObjectURL).toHaveBeenCalledWith(file)
    })

    it('should not update for non-image files', async () => {
      const user = userEvent.setup()
      render(<Profile_Workspace user={mockUser} />)

      const file = new File(['dummy content'], 'document.txt', { type: 'text/plain' })
      const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement

      await user.upload(fileInput, file)

      expect(globalThis.URL.createObjectURL).not.toHaveBeenCalled()
    })
  })
})