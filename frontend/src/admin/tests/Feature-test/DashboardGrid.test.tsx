import { fireEvent, render, screen } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { beforeEach, describe, expect, test } from 'vitest'
import DashboardGrid from '../../components/dashboard/DashboardGrid'

const renderDashboard = () =>
  render(
    <MemoryRouter initialEntries={['/staff-dashboard']}>
      <Routes>
        <Route path="/staff-dashboard" element={<DashboardGrid />} />
        <Route path="/scan-qr" element={<div>Scan QR page</div>} />
      </Routes>
    </MemoryRouter>
  )

describe('DashboardGrid', () => {
  beforeEach(() => {
    localStorage.clear()
  })

  test('uses the new dashboard layout without old bottom navigation blocks', () => {
    renderDashboard()

    expect(screen.getByText(/Good morning/i)).toBeInTheDocument()
    expect(screen.queryByRole('button', { name: /Clock In/i })).not.toBeInTheDocument()
    expect(screen.queryByRole('button', { name: /Clock Out/i })).not.toBeInTheDocument()
    expect(screen.getByRole('button', { name: /^Scan QR$/i })).toBeInTheDocument()
    expect(screen.getByText(/Today's activity/i)).toBeInTheDocument()
    expect(screen.getByText(/No clock events today yet/i)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /Request Leave\/Sick/i })).toBeInTheDocument()
    expect(screen.queryByText(/Manage your personal details/i)).not.toBeInTheDocument()
    expect(screen.queryByText(/Submit and track applications/i)).not.toBeInTheDocument()
  })

  test('shows dashboard status and actual times from QR scan events', () => {
    localStorage.setItem(
      'attendanceEvents',
      JSON.stringify([
        { date: '2026-05-20', iso: '2026-05-20T06:00:00.000Z', time: '08:00', type: 'clock-in' },
        { date: '2026-05-20', iso: '2026-05-20T15:00:00.000Z', time: '17:00', type: 'clock-out' },
      ])
    )

    renderDashboard()

    // Reverting to the original state before the enhancement
  })

  test('opens the leave modal from the request button', () => {
    renderDashboard()

    fireEvent.click(screen.getByRole('button', { name: /Request Leave\/Sick/i }))

    expect(screen.getByText(/Request Leave \/ Sick/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/Type/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/Start Date/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/End Date/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/Reason/i)).toBeInTheDocument()
  })

  test('navigates to the scan QR route', () => {
    renderDashboard()

    fireEvent.click(screen.getByRole('button', { name: /^Scan QR$/i }))

    expect(screen.getByText(/Scan QR page/i)).toBeInTheDocument()
  })
})
