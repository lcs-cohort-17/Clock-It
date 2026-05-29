import '@testing-library/jest-dom/vitest'
import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import ScanQRPage from '../../pages/ScanQRPage'

describe('ScanQRPage', () => {
  it('renders the scan qr page content', () => {
    render(<ScanQRPage />)

    expect(screen.getByRole('heading', { name: 'Scan QR Code' })).toBeInTheDocument()
    expect(
      screen.getByText('Display this workplace QR code so staff can scan it from their phone.'),
    ).toBeInTheDocument()
    expect(screen.getByText('Workplace QR Code')).toBeInTheDocument()
    expect(screen.getByText('CLOCK-IT-SITE-001')).toBeInTheDocument()
  })
})
