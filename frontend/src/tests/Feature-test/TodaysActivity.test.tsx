import { render, screen } from '@testing-library/react'
import { describe, expect, test } from 'vitest'
import TodaysActivity from '../../components/Features/TodaysActivity'

describe('TodaysActivity', () => {
  test('shows fallback when no activity', () => {
    render(<TodaysActivity activity={null} />)
    expect(screen.getByText(/No clock events today/i)).toBeInTheDocument()
  })

  test('renders activity details', () => {
    render(
      <TodaysActivity
        activity={{
          firstClockIn: '08:00',
          lastClockOut: '17:00',
          totalHours: 9,
        }}
      />
    )

    expect(screen.getByText(/First clock-in/i)).toBeInTheDocument()
    expect(screen.getByText(/Last clock-out/i)).toBeInTheDocument()
    expect(screen.getByText(/Total hours: 9/i)).toBeInTheDocument()
  })
})
