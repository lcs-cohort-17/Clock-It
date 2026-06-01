import '@testing-library/jest-dom/vitest'
import { render, screen } from '@testing-library/react'
import { describe, expect, it, vi } from 'vitest'
import ScanQRCard, { SCAN_QR_TEXT } from './ScanQRCard'

vi.mock('html5-qrcode', () => ({
  Html5Qrcode: vi.fn().mockImplementation(() => ({
    start: vi.fn(),
    stop: vi.fn().mockResolvedValue(undefined),
    clear: vi.fn(),
  })),
}))

describe('ScanQRCard', () => {
  it('renders the default scan card content', () => {
    render(<ScanQRCard />)

    expect(screen.getByText(SCAN_QR_TEXT.badge)).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: SCAN_QR_TEXT.title })).toBeInTheDocument()
    expect(screen.getByText(SCAN_QR_TEXT.description)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: SCAN_QR_TEXT.openButton })).toBeInTheDocument()
  })
})
