import '@testing-library/jest-dom/vitest'
import { cleanup, render, screen } from '@testing-library/react'
import { afterEach, describe, expect, it } from 'vitest'
import App from './App'

describe('Clock It dashboard smoke test', () => {
  afterEach(() => {
    cleanup()
  })

  it('renders the current dashboard instead of the old login screen', () => {
    render(<App />)

    expect(screen.getByRole('heading', { name: 'Clocked Out' })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: 'Sign in' })).not.toBeInTheDocument()
  })
})
