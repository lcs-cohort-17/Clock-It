import { describe, it, expect, beforeEach, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Support_Workspace } from '../../components/profile/Support_Workspace'

describe('Support_Workspace', () => {
  const openSpy = vi
    .spyOn(window, 'open')
    .mockImplementation(() => null)

  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('Rendering', () => {
    it('should render support section header', () => {
      render(<Support_Workspace />)
      expect(screen.getByText('Support & Data')).toBeInTheDocument()
      expect(screen.getByText('Manage support requests and local application data.')).toBeInTheDocument()
    })

    it('should render contact admin button', () => {
      render(<Support_Workspace />)
      expect(screen.getByText('Contact admin')).toBeInTheDocument()
    })

    it('should render version info', () => {
      render(<Support_Workspace />)
      expect(screen.getByText(/App version/)).toBeInTheDocument()
      expect(screen.getByText('1.0.0')).toBeInTheDocument()
    })

    it('should render clear local cache button', () => {
      render(<Support_Workspace />)
      expect(screen.getByText('Clear local cache')).toBeInTheDocument()
    })
  })

  describe('Contact Admin', () => {
    it('should open mail client when clicking contact admin', async () => {
      const user = userEvent.setup()
      render(<Support_Workspace />)

      const contactButton = screen.getByRole('button', {
        name: 'Contact admin',
      })
      await user.click(contactButton)

      expect(openSpy).toHaveBeenCalledWith(
        'mailto:admin@clock-it.com?subject=Clock-It Support Request'
      )
    })

    it('should show loading state when clicking contact admin', async () => {
      const user = userEvent.setup()
      render(<Support_Workspace />)

      const contactButton = screen.getByRole('button', {
        name: 'Contact admin',
      })
      await user.click(contactButton)

      expect(screen.getByText('Opening support...')).toBeInTheDocument()
      expect(contactButton).toBeDisabled()
    })
  })

  describe('Clear Local Cache', () => {
    it('should open confirmation modal when clicking clear cache', async () => {
      const user = userEvent.setup()
      render(<Support_Workspace />)

      await user.click(screen.getByText('Clear local cache'))

      expect(screen.getByText('Clear local cache?')).toBeInTheDocument()
      expect(screen.getByText('Yes, clear cache')).toBeInTheDocument()
      expect(screen.getByText('Cancel')).toBeInTheDocument()
    })

    it('should close modal when clicking cancel', async () => {
      const user = userEvent.setup()
      render(<Support_Workspace />)

      await user.click(screen.getByText('Clear local cache'))
      expect(screen.getByText('Clear local cache?')).toBeInTheDocument()

      await user.click(screen.getByText('Cancel'))
      expect(screen.queryByText('Clear local cache?')).not.toBeInTheDocument()
    })

    it('should clear localStorage when confirmed', async () => {
      const user = userEvent.setup()
      localStorage.setItem('offline-attendance', 'test-data')

      render(<Support_Workspace />)

      await user.click(screen.getByText('Clear local cache'))
      await user.click(screen.getByText('Yes, clear cache'))

      expect(localStorage.getItem('offline-attendance')).toBeNull()
    })
  })
})
