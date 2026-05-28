import '@testing-library/jest-dom/vitest'
import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import ScanQRCard, { SCAN_QR_TEXT } from '../../components/ScanQRCard'

describe('ScanQRCard', () => {
  it('renders the workplace QR display card content', () => {
    render(<ScanQRCard />)

    expect(screen.getByText(SCAN_QR_TEXT.badge)).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: SCAN_QR_TEXT.title })).toBeInTheDocument()
    expect(screen.getByText(SCAN_QR_TEXT.description)).toBeInTheDocument()
    expect(screen.getByText(SCAN_QR_TEXT.codeValue)).toBeInTheDocument()
    expect(screen.getByText(SCAN_QR_TEXT.helperText)).toBeInTheDocument()
  })
})
