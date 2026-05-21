// import '@testing-library/jest-dom/vitest'
// import { render, screen } from '@testing-library/react'
// import userEvent from '@testing-library/user-event'
// import { BrowserRouter, MemoryRouter, Route, Routes } from 'react-router-dom'
// import { describe, expect, test, vi } from 'vitest'

// import Header from '../tests/Dashboard-test/Header'
// import Sidebar from './components/dashboard/Sidebar'
// import DashboardGrid from './components/dashboard/DashboardGrid'
// import DashboardPage from './DashboardPage'

// describe('Dashboard frontend tests', () => {
//   test('renders sidebar navigation items', () => {
//     render(
//       <BrowserRouter>
//         <Sidebar isOpen={true} />
//       </BrowserRouter>,
//     )

//     expect(screen.getByText('Dashboard')).toBeInTheDocument()
//     expect(screen.getByText('Scan QR')).toBeInTheDocument()
//     expect(screen.getByText('History')).toBeInTheDocument()
//     expect(screen.getByText('Profile')).toBeInTheDocument()
//     expect(screen.getByText('Log out')).toBeInTheDocument()
//   })

//   test('renders dashboard grid content', () => {
//     render(<DashboardGrid />)

//     expect(screen.getByText(/current status/i)).toBeInTheDocument()
//     expect(screen.getByText(/clocked out/i)).toBeInTheDocument()
//     expect(screen.getByText(/scan qr code to clock in/i)).toBeInTheDocument()
//     expect(screen.getByText(/calendar/i)).toBeInTheDocument()
//     expect(screen.getByText(/leave requests/i)).toBeInTheDocument()
//     expect(screen.getByText(/profile/i)).toBeInTheDocument()
//   })

//   test('mobile menu button calls onMenuClick', async () => {
//     const user = userEvent.setup()
//     const onMenuClick = vi.fn()

//     render(<Header onMenuClick={onMenuClick} />)

//     const menuButton = screen.getByRole('button')
//     await user.click(menuButton)

//     expect(onMenuClick).toHaveBeenCalledTimes(1)
//   })

//   test('sidebar close button calls onClose', async () => {
//     const user = userEvent.setup()
//     const onClose = vi.fn()

//     render(
//       <BrowserRouter>
//         <Sidebar isOpen={true} onClose={onClose} />
//       </BrowserRouter>,
//     )

//     const closeButton = screen.getByRole('button', { name: '×' })
//     await user.click(closeButton)

//     expect(onClose).toHaveBeenCalledTimes(1)
//   })

//   test('logout interaction navigates to login page', async () => {
//     const user = userEvent.setup()

//     render(
//       <MemoryRouter initialEntries={['/staff-dashboard']}>
//         <Routes>
//           <Route path="/" element={<h1>Login Page</h1>} />
//           <Route path="/staff-dashboard" element={<Sidebar isOpen={true} />} />
//         </Routes>
//       </MemoryRouter>,
//     )

//     await user.click(screen.getByText(/log out/i))

//     expect(screen.getByText(/login page/i)).toBeInTheDocument()
//   })

//   test('dashboard page renders protected dashboard content', () => {
//     render(
//       <BrowserRouter>
//         <DashboardPage />
//       </BrowserRouter>,
//     )

//     expect(screen.getByText(/staff/i)).toBeInTheDocument()
//     expect(screen.getByText(/clocked out/i)).toBeInTheDocument()
//     expect(screen.getByText(/sarah mthembu/i)).toBeInTheDocument()
//   })
// })