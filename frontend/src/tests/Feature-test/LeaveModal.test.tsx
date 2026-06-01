import { render, screen, fireEvent } from '@testing-library/react'
import { beforeEach, describe, expect, test, vi } from 'vitest'
import LeaveModal from '../../components/Features/LeaveModal'

describe('LeaveModal', () => {
  const onClose = vi.fn()
  const onSubmit = vi.fn()

  const renderModal = (isOpen = true) =>
    render(
      <LeaveModal
        isOpen={isOpen}
        onClose={onClose}
        onSubmit={onSubmit}
      />
    )

  beforeEach(() => {
    vi.clearAllMocks()
    localStorage.clear()
  })

  test('does not render when closed', () => {
    renderModal(false)
    expect(screen.queryByText(/Request Leave \/ Sick/i)).not.toBeInTheDocument()
  })

  test('renders modal when open', () => {
    renderModal()
    expect(screen.getByText(/Request Leave \/ Sick/i)).toBeInTheDocument()
  })

  test('validates missing dates', () => {
    renderModal()

    fireEvent.click(screen.getByText(/Submit/i))

    expect(screen.getByText(/Start and end date are required/i)).toBeInTheDocument()
    expect(onSubmit).not.toHaveBeenCalled()
  })

  test('validates end date before start date', () => {
    renderModal()

    fireEvent.change(screen.getByLabelText(/Start Date/i), {
      target: { value: '2026-05-10' },
    })

    fireEvent.change(screen.getByLabelText(/End Date/i), {
      target: { value: '2026-05-01' },
    })

    fireEvent.click(screen.getByText(/Submit/i))

    expect(screen.getByText(/End date must be after or equal/i)).toBeInTheDocument()
  })

  test('submits valid request', () => {
    renderModal()

    fireEvent.change(screen.getByLabelText(/Start Date/i), {
      target: { value: '2099-01-01' },
    })

    fireEvent.change(screen.getByLabelText(/End Date/i), {
      target: { value: '2099-01-02' },
    })

    fireEvent.change(screen.getByLabelText(/Reason/i), {
      target: { value: 'Medical appointment' },
    })

    fireEvent.click(screen.getByText(/Submit/i))

    expect(onSubmit).toHaveBeenCalledOnce()
    expect(onClose).toHaveBeenCalledOnce()
    expect(JSON.parse(localStorage.getItem('leaveRequests') ?? '[]')).toEqual([
      {
        type: 'Leave',
        start_date: '2099-01-01',
        end_date: '2099-01-02',
        reason: 'Medical appointment',
      },
    ])
  })
})
