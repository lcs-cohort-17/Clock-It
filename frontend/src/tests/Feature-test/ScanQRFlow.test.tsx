import { fireEvent, render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import ScanQRFlow from '../../components/Features/ScanQRFlow'

describe('ScanQRFlow', () => {
  beforeEach(() => {
    localStorage.clear()
    vi.useFakeTimers()
    vi.setSystemTime(new Date('2026-05-20T06:30:00.000Z'))
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  test('stores a clock-in scan with the actual scan time', () => {
    render(
      <MemoryRouter>
        <ScanQRFlow />
      </MemoryRouter>
    )

    fireEvent.change(screen.getByLabelText(/QR scan code/i), {
      target: { value: 'CLOCK_IN' },
    })
    fireEvent.click(screen.getByRole('button', { name: /Submit scan/i }))

    const events = JSON.parse(localStorage.getItem('attendanceEvents') ?? '[]')
    expect(events).toEqual([
      expect.objectContaining({
        date: '2026-05-20',
        type: 'clock-in',
      }),
    ])
    expect(screen.getByText(/Clocked in at/i)).toBeInTheDocument()
  })

  test('rejects invalid QR codes', () => {
    render(
      <MemoryRouter>
        <ScanQRFlow />
      </MemoryRouter>
    )

    fireEvent.change(screen.getByLabelText(/QR scan code/i), {
      target: { value: 'BAD_CODE' },
    })
    fireEvent.click(screen.getByRole('button', { name: /Submit scan/i }))

    expect(screen.getByText(/Invalid QR code/i)).toBeInTheDocument()
    expect(localStorage.getItem('attendanceEvents')).toBeNull()
  })
})
