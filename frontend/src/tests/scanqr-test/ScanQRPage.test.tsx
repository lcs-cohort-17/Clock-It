import '@testing-library/jest-dom/vitest'
import { render, screen } from '@testing-library/react'
import { describe, expect, it, vi } from 'vitest'
import ScanQRPage from './ScanQRPage'

vi.mock('html5-qrcode', () => ({
  Html5Qrcode: vi.fn().mockImplementation(() => ({
    start: vi.fn(),
    stop: vi.fn().mockResolvedValue(undefined),
    clear: vi.fn(),
  })),
}))

describe('ScanQRPage', () => {
  it('renders the scan qr page content', () => {
    render(<ScanQRPage />)

    expect(screen.getByRole('heading', { name: 'Scan QR Code' })).toBeInTheDocument()
    expect(
      screen.getByText('Point your camera at the workplace QR code to clock in or out.'),
    ).toBeInTheDocument()
    expect(screen.getByText('Ready to scan')).toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Open camera' })).toBeInTheDocument()
  })
})