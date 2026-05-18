import '@testing-library/jest-dom/vitest'
import { cleanup, render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { afterEach, describe, expect, it } from 'vitest'
import App from './App'

describe('Clock It dashboard', () => {
  afterEach(() => {
    cleanup()
  })

  it('renders the dashboard shell and primary clock action', () => {
    render(<App />)

    expect(screen.getAllByRole('heading', { name: 'Clock-It' })).toHaveLength(2)
    expect(screen.getByText('Welcome, Sibahle')).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: 'Clocked Out' })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Scan QR Code to Clock In' })).toBeInTheDocument()
  })

  it('renders dashboard navigation and cards', () => {
    render(<App />)

    expect(screen.getByRole('button', { name: 'Dashboard' })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Attendance History' })).toBeInTheDocument()
    expect(screen.getByText('Calendar')).toBeInTheDocument()
    expect(screen.getByText('Leave Requests')).toBeInTheDocument()
    expect(screen.getByText('Manage your personal details.')).toBeInTheDocument()
  })

  it('opens the profile page from the sidebar profile button', async () => {
    const user = userEvent.setup()
    render(<App />)

    await user.click(screen.getByRole('button', { name: 'Profile' }))

    expect(screen.getByRole('heading', { name: 'User Profile' })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: 'Sibahle' })).toBeInTheDocument()
    expect(screen.getAllByText('sibahle@clockit.app')[0]).toBeInTheDocument()
    expect(screen.getByText('CLK-014')).toBeInTheDocument()
    expect(screen.getByText('Staff')).toBeInTheDocument()
  })

  it('returns from the profile page to the dashboard', async () => {
    const user = userEvent.setup()
    render(<App />)

    await user.click(screen.getByRole('button', { name: 'Profile' }))
    await user.click(screen.getByRole('button', { name: 'Dashboard' }))

    expect(screen.getByRole('heading', { name: 'Clocked Out' })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: 'User Profile' })).not.toBeInTheDocument()
  })
})
