import '@testing-library/jest-dom/vitest'
import { act, cleanup, render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { afterEach, beforeEach, describe, expect, it } from 'vitest'
import App from '../App'
import mockUsers from './mock_data/users.json'

function setNavigatorOnline(isOnline: boolean) {
  Object.defineProperty(window.navigator, 'onLine', {
    configurable: true,
    value: isOnline,
  })
}

describe('Clock It login page', () => {
  beforeEach(() => {
    setNavigatorOnline(true)
  })

  afterEach(() => {
    cleanup()
  })

  it('renders the approved authentication content', () => {
    render(<App />)

    expect(screen.getAllByText('Clock It')[0]).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: 'Sign in' })).toBeInTheDocument()
    expect(screen.getByText('Use the credentials provided by your administrator.')).toBeInTheDocument()
    expect(screen.getByRole('status')).toHaveTextContent('Connected')
    expect(screen.getByLabelText('Email')).toHaveValue('taaraa.yang.banhg@clockit.app')
    expect(screen.getByLabelText('Password')).toHaveAttribute('type', 'password')
    expect(screen.getByText('Demo Accounts')).toBeInTheDocument()
    expect(screen.getByText('Taaraa Yang Banhg')).toBeInTheDocument()
  })

  it('loads unique mock users from JSON data', () => {
    const mockUserNames = mockUsers.map((user) => user.name)
    const mockPasswords = mockUsers.map((user) => user.password)

    expect(mockUsers.length).toBeGreaterThan(1)
    expect(mockUserNames).toContain('Taaraa Yang Banhg')
    expect(new Set(mockUserNames).size).toBe(mockUsers.length)
    expect(new Set(mockPasswords).size).toBe(mockUsers.length)
    expect(
      mockUsers.every((user) =>
        user.password.toLowerCase().includes(user.name.split(' ')[0].toLowerCase()),
      ),
    ).toBe(true)
  })

  it('shows required-field validation errors', async () => {
    const user = userEvent.setup()
    render(<App />)

    await user.clear(screen.getByLabelText('Email'))
    await user.clear(screen.getByLabelText('Password'))
    await user.click(screen.getByRole('button', { name: 'Sign in' }))

    expect(screen.getByText('Email is required.')).toBeInTheDocument()
    expect(screen.getByText('Password is required.')).toBeInTheDocument()
  })

  it('shows an invalid email validation error', async () => {
    const user = userEvent.setup()
    render(<App />)

    await user.clear(screen.getByLabelText('Email'))
    await user.type(screen.getByLabelText('Email'), 'admin')
    await user.click(screen.getByRole('button', { name: 'Sign in' }))

    expect(screen.getByText('Enter a valid email address.')).toBeInTheDocument()
  })

  it('shows loading and incorrect credential feedback', async () => {
    const user = userEvent.setup()
    render(<App />)

    await user.clear(screen.getByLabelText('Password'))
    await user.type(screen.getByLabelText('Password'), 'wrong-password')
    await user.click(screen.getByRole('button', { name: 'Sign in' }))

    expect(screen.getByRole('button', { name: 'Signing in...' })).toBeDisabled()

    expect(
      await screen.findByText('Incorrect email or password. Please check your Clock It credentials.'),
    ).toBeInTheDocument()
  })

  it('keeps remember me selectable and exposes the forgot password link', async () => {
    const user = userEvent.setup()
    render(<App />)

    const rememberMe = screen.getByLabelText('Remember me')
    await user.click(rememberMe)

    expect(rememberMe).toBeChecked()
    const forgotPasswordLink = screen.getByRole('link', { name: 'Forgot password?' })
    const forgotPasswordHref = forgotPasswordLink.getAttribute('href')

    expect(forgotPasswordHref).toContain('mailto:support@clockit.app')
    expect(forgotPasswordHref).toContain('password%20reset')
    expect(forgotPasswordHref).toContain('taaraa.yang.banhg%40clockit.app')
  })

  it('updates the internet connection indicator', () => {
    render(<App />)

    expect(screen.getByRole('status')).toHaveTextContent('Connected')

    setNavigatorOnline(false)
    act(() => window.dispatchEvent(new Event('offline')))

    expect(screen.getByRole('status')).toHaveTextContent('Offline')

    setNavigatorOnline(true)
    act(() => window.dispatchEvent(new Event('online')))

    expect(screen.getByRole('status')).toHaveTextContent('Connected')
  })

  it('accepts approved demo credentials without showing a credential error', async () => {
    const user = userEvent.setup()
    render(<App />)

    await user.click(screen.getByRole('button', { name: 'Sign in' }))

    await waitFor(() => expect(screen.getByRole('button', { name: 'Sign in' })).toBeEnabled())
    expect(
      screen.queryByText('Incorrect email or password. Please check your Clock It credentials.'),
    ).not.toBeInTheDocument()
  })
})
