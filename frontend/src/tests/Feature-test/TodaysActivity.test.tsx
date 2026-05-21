import { render, screen } from '@testing-library/react'
import { describe, expect, test } from 'vitest'
import TodaysActivity from '../../components/Features/TodaysActivity'
import attendanceMock from './attendanceMock.json'

describe('TodaysActivity', () => {
  test('shows fallback when no activity', () => {
    render(<TodaysActivity activity={null} />)
    expect(screen.getByText(/No clock events today/i)).toBeInTheDocument()
  })

  test('renders activity details', () => {
    render(
      <TodaysActivity
        activity={attendanceMock.todaysActivity}
      />
    )

    expect(screen.getByText(/First clock-in/i)).toBeInTheDocument()
    expect(screen.getByText(/Last clock-out/i)).toBeInTheDocument()
    expect(screen.getByText(`Total hours: ${attendanceMock.todaysActivity.totalHours}`)).toBeInTheDocument()
  })

  test('handles invalid activity data gracefully', () => {
    render(<TodaysActivity activity={{}} />)
    expect(screen.getByText(/No clock events today/i)).toBeInTheDocument()
  })

  test('handles incomplete activity data', () => {
    render(
      <TodaysActivity
        activity={{ firstClockIn: '08:00', totalHours: null }}
      />
    )
    expect(screen.getByText(/First clock-in/i)).toBeInTheDocument()
    expect(screen.queryByText(/Total hours:/i)).not.toBeInTheDocument()
  })
})
