import '@testing-library/jest-dom/vitest'
import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import ScanQRCard from '../../components/ScanQRCard'

describe('ScanQRCard', () => {
  it('renders the workplace QR display card content', () => {
    render(<ScanQRCard />)

    expect(screen.getByText('Badge')).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: 'Scan QR code' })).toBeInTheDocument()
    expect(screen.getByText('Scan this QR code at the workplace entrance.')).toBeInTheDocument()
    expect(screen.getByText('WKP-8472-91')).toBeInTheDocument()
    expect(screen.getByText('Keep this code visible while entering.')).toBeInTheDocument()
  })
})
