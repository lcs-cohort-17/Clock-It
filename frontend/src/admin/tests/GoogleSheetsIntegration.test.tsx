import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import GoogleSheetsIntegration from '../components/AdminSettingsComponents/GoogleSheetsIntegration'

describe('GoogleSheetsIntegration', () => {
  it('renders the integration block', () => {
    render(<GoogleSheetsIntegration />)
    expect(screen.getByText('Google Sheets Integration')).toBeTruthy()
  })
})
