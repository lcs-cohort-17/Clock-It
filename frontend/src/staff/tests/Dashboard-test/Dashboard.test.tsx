import '@testing-library/jest-dom/vitest'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { BrowserRouter } from 'react-router-dom'
import { beforeEach, describe, expect, test, vi } from 'vitest'

import App from '../../App'
import Header from '../../components/dashboard/Header'
import Sidebar from '../../components/dashboard/Sidebar'
import DashboardGrid from '../../components/dashboard/DashboardGrid'



const mockUser = {
  fullName: 'Shaheed Karlie',
  email: 'shaheed@clockit.com',
  employeeId: 'EMP001',
  role: 'staff',
}

function renderAppAt(path: string) {
  window.history.pushState({}, '', path)
  return render(<App />)
}

beforeEach(() => {
  localStorage.clear()

  Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: vi.fn().mockImplementation((query) => ({
      matches: false,
      media: query,
      onchange: null,
      addListener: vi.fn(),
      removeListener: vi.fn(),
      addEventListener: vi.fn(),
      removeEventListener: vi.fn(),
      dispatchEvent: vi.fn(),
    })),
  })
})

describe('Dashboard frontend tests', () => {
  test('renders sidebar navigation items', () => {
    render(
      <BrowserRouter>
        <Sidebar isOpen={true} user={mockUser} />
      </BrowserRouter>,
    )

    expect(screen.getByText('Dashboard')).toBeInTheDocument()
    expect(screen.getByText('Scan QR')).toBeInTheDocument()
    expect(screen.getByText('History')).toBeInTheDocument()
    expect(screen.getByText('Profile')).toBeInTheDocument()
    expect(screen.getByText('Log out')).toBeInTheDocument()
  })

  test('renders dashboard grid content with logged-in user name', () => {
    render(
      <BrowserRouter>
        <DashboardGrid user={mockUser} />
      </BrowserRouter>,
    )

    expect(screen.getByText(/good morning,\s*shaheed/i)).toBeInTheDocument()
    //expect(screen.getByText(/good morning, shaheed/i)).toBeInTheDocument()
    expect(screen.getByText(/scan qr/i)).toBeInTheDocument()
  })

 test('mobile menu button calls onMenuClick', async () => {
  const user = userEvent.setup()
  const onMenuClick = vi.fn()

  render(<Header onMenuClick={onMenuClick} />)

  const buttons = screen.getAllByRole('button')

  await user.click(buttons[0])

  expect(onMenuClick).toHaveBeenCalledTimes(1)
})

  test('sidebar close button calls onClose', async () => {
    const user = userEvent.setup()
    const onClose = vi.fn()

    render(
      <BrowserRouter>
        <Sidebar isOpen={true} onClose={onClose} user={mockUser} />
      </BrowserRouter>,
    )

    await user.click(screen.getByRole('button', { name: '×' }))

    expect(onClose).toHaveBeenCalledTimes(1)
  })

  test('logout interaction navigates to login page', async () => {
    const user = userEvent.setup()

    localStorage.setItem('loggedInUser', JSON.stringify(mockUser))
    renderAppAt('/staff-dashboard')

    await user.click(screen.getByText(/log out/i))

    expect(window.location.pathname).toBe('/')
  })

  test('sidebar and header show on dashboard page', () => {
    localStorage.setItem('loggedInUser', JSON.stringify(mockUser))

    renderAppAt('/staff-dashboard')

    expect(screen.getByText(/clock it/i)).toBeInTheDocument()
    expect(screen.getByText(/staff/i)).toBeInTheDocument()
    expect(screen.getByText(/shaheed karlie/i)).toBeInTheDocument()
    expect(screen.getByText(/shaheed@clockit.com/i)).toBeInTheDocument()
  })

  test('sidebar and header show on history page', () => {
    localStorage.setItem('loggedInUser', JSON.stringify(mockUser))

    renderAppAt('/history')

    expect(screen.getByText(/dashboard/i)).toBeInTheDocument()
    expect(screen.getByText(/scan qr/i)).toBeInTheDocument()
    expect(screen.getAllByText(/history/i).length).toBeGreaterThan(0)
    expect(screen.getByText(/log out/i)).toBeInTheDocument()
  })

  test('sidebar and header show on profile page', () => {
    localStorage.setItem('loggedInUser', JSON.stringify(mockUser))

    renderAppAt('/profile')

    expect(screen.getByText(/dashboard/i)).toBeInTheDocument()
    expect(screen.getByText(/scan qr/i)).toBeInTheDocument()
    expect(screen.getAllByText(/profile/i).length).toBeGreaterThan(0)
    expect(screen.getByText(/log out/i)).toBeInTheDocument()
  })

//   test('unauthorized user does not see protected dashboard content', () => {
//     renderAppAt('/staff-dashboard')

//     expect(screen.queryByText(/shaheed karlie/i)).not.toBeInTheDocument()
//     expect(screen.queryByText(/log out/i)).not.toBeInTheDocument(){{}}__
//   })/
})