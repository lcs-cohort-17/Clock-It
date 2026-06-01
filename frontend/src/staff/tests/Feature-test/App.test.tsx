import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import App from '../../App'

describe('<App />', () => {
  it('renders the login landing state', () => {
    render(<App />)

    expect(screen.getByRole('heading', { name: 'Clock It' })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /Sign in to Clock It/i })).toBeInTheDocument()
    expect(screen.queryByRole('button', { name: /scan qr code to clock in/i })).not.toBeInTheDocument()
    expect(screen.queryByText('Leave Requests')).not.toBeInTheDocument()
  })
})
