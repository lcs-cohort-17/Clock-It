import { fireEvent, render, screen } from '@testing-library/react'
import { describe, expect, test } from 'vitest'
import Calendar from '../../components/Features/Calendar'

describe('Calendar', () => {
  test('shows the current month', () => {
    render(<Calendar />)
    const currentMonth = new Date().toLocaleString('default', {
      month: 'long',
      year: 'numeric',
    })

    expect(screen.getByText(currentMonth)).toBeInTheDocument()
  })

  test('highlights dates with attendance events and shows a summary on click', () => {
    render(
      <Calendar
        events={[
          { date: '2026-05-20', type: 'clock-in', time: '08:00' },
          { date: '2026-05-20', type: 'clock-out', time: '17:00' },
        ]}
      />
    )

    fireEvent.click(screen.getByRole('button', { name: /2026-05-20 has attendance events/i }))

    expect(screen.getAllByLabelText(/attendance event/i).length).toBeGreaterThan(0)
    expect(screen.getByText(/Clock in: 08:00/i)).toBeInTheDocument()
    expect(screen.getByText(/Clock out: 17:00/i)).toBeInTheDocument()
  })

  test('handles overlapping events on the same day', () => {
    render(
      <Calendar
        events={[
          { date: '2026-05-20', type: 'clock-in', time: '08:00' },
          { date: '2026-05-20', type: 'clock-in', time: '09:00' },
        ]}
      />
    )

    fireEvent.click(screen.getByRole('button', { name: /2026-05-20 has attendance events/i }))

    expect(screen.getAllByLabelText(/attendance event/i).length).toBe(2)
    expect(screen.getByText(/Clock in: 08:00/i)).toBeInTheDocument()
    expect(screen.getByText(/Clock in: 09:00/i)).toBeInTheDocument()
  })

  test('handles invalid dates gracefully', () => {
    render(<Calendar events={[{ date: 'invalid-date', type: 'clock-in', time: '08:00' }]} />)

    expect(screen.queryByRole('button', { name: /invalid-date/i })).not.toBeInTheDocument()
  })
})
