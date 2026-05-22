import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import PasswordSection from './PasswordSection'

describe('PasswordSection', () => {
  /* =========================
     RENDER TESTS
  ========================= */

  test('renders password section heading', () => {
    render(<PasswordSection />)

    expect(
      screen.getByText(/change password/i)
    ).toBeInTheDocument()
  })

  test('renders all password inputs', () => {
    render(<PasswordSection />)

    expect(
      screen.getByLabelText(/current password/i)
    ).toBeInTheDocument()

    expect(
      screen.getByLabelText(/^new password$/i)
    ).toBeInTheDocument()

    expect(
      screen.getByLabelText(
        /confirm new password/i
      )
    ).toBeInTheDocument()
  })

  test('renders update password button', () => {
    render(<PasswordSection />)

    expect(
      screen.getByRole('button', {
        name: /update password/i,
      })
    ).toBeInTheDocument()
  })

  /* =========================
     INPUT TESTS
  ========================= */

  test('allows typing into current password field', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    const input =
      screen.getByLabelText(
        /current password/i
      )

    await user.type(input, 'OldPass123!')

    expect(input).toHaveValue('OldPass123!')
  })

  test('allows typing into new password field', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    const input =
      screen.getByLabelText(/^new password$/i)

    await user.type(input, 'NewPass123!')

    expect(input).toHaveValue('NewPass123!')
  })

  test('allows typing into confirm password field', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    const input =
      screen.getByLabelText(
        /confirm new password/i
      )

    await user.type(input, 'NewPass123!')

    expect(input).toHaveValue('NewPass123!')
  })

  /* =========================
     VALIDATION TESTS
  ========================= */

  test('shows error when fields are empty', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    await user.click(
      screen.getByTestId('password-submit')
    )

    expect(
      screen.getByText(
        /please fill in all fields/i
      )
    ).toBeInTheDocument()
  })

  test('shows error when password is weak', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    await user.type(
      screen.getByLabelText(
        /current password/i
      ),
      'OldPass123!'
    )

    await user.type(
      screen.getByLabelText(/^new password$/i),
      'abc'
    )

    await user.type(
      screen.getByLabelText(
        /confirm new password/i
      ),
      'abc'
    )

    await user.click(
      screen.getByTestId('password-submit')
    )

    expect(
      screen.getByText(
        /password must include uppercase/i
      )
    ).toBeInTheDocument()
  })

  test('shows error when passwords do not match', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    await user.type(
      screen.getByLabelText(
        /current password/i
      ),
      'OldPass123!'
    )

    await user.type(
      screen.getByLabelText(/^new password$/i),
      'NewPass123!'
    )

    await user.type(
      screen.getByLabelText(
        /confirm new password/i
      ),
      'WrongPass123!'
    )

    await user.click(
      screen.getByTestId('password-submit')
    )

    expect(
      screen.getByText(/passwords do not match/i)
    ).toBeInTheDocument()
  })

  /* =========================
     SUCCESS TESTS
  ========================= */

  test('shows success message for valid password update', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    await user.type(
      screen.getByLabelText(
        /current password/i
      ),
      'OldPass123!'
    )

    await user.type(
      screen.getByLabelText(/^new password$/i),
      'NewPass123!'
    )

    await user.type(
      screen.getByLabelText(
        /confirm new password/i
      ),
      'NewPass123!'
    )

    await user.click(
      screen.getByTestId('password-submit')
    )

    expect(
      screen.getByText(
        /password updated successfully/i
      )
    ).toBeInTheDocument()
  })

  test('clears all fields after successful submit', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    const current =
      screen.getByLabelText(
        /current password/i
      )

    const next =
      screen.getByLabelText(/^new password$/i)

    const confirm =
      screen.getByLabelText(
        /confirm new password/i
      )

    await user.type(current, 'OldPass123!')
    await user.type(next, 'NewPass123!')
    await user.type(confirm, 'NewPass123!')

    await user.click(
      screen.getByTestId('password-submit')
    )

    expect(current).toHaveValue('')
    expect(next).toHaveValue('')
    expect(confirm).toHaveValue('')
  })

  /* =========================
     PASSWORD STRENGTH TESTS
  ========================= */

  test('shows weak password strength', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    await user.type(
      screen.getByLabelText(/^new password$/i),
      'abc'
    )

    expect(
      screen.getByText(
        /password strength: weak/i
      )
    ).toBeInTheDocument()
  })

  test('shows medium password strength', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    await user.type(
      screen.getByLabelText(/^new password$/i),
      'Password1'
    )

    expect(
      screen.getByText(
        /password strength: medium/i
      )
    ).toBeInTheDocument()
  })

  test('shows strong password strength', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    await user.type(
      screen.getByLabelText(/^new password$/i),
      'Password1!'
    )

    expect(
      screen.getByText(
        /password strength: strong/i
      )
    ).toBeInTheDocument()
  })

  /* =========================
     TOGGLE VISIBILITY TESTS
  ========================= */

  test('toggles current password visibility', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    const input =
      screen.getByLabelText(
        /current password/i
      )

    const toggle =
      screen.getByTestId(
        'current-password-toggle-button'
      )

    expect(input).toHaveAttribute(
      'type',
      'password'
    )

    await user.click(toggle)

    expect(input).toHaveAttribute(
      'type',
      'text'
    )
  })

  test('toggles new password visibility', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    const input =
      screen.getByLabelText(/^new password$/i)

    const toggle =
      screen.getByTestId(
        'new-password-toggle-button'
      )

    expect(input).toHaveAttribute(
      'type',
      'password'
    )

    await user.click(toggle)

    expect(input).toHaveAttribute(
      'type',
      'text'
    )
  })

  test('toggles confirm password visibility', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    const input =
      screen.getByLabelText(
        /confirm new password/i
      )

    const toggle =
      screen.getByTestId(
        'confirm-password-toggle-button'
      )

    expect(input).toHaveAttribute(
      'type',
      'password'
    )

    await user.click(toggle)

    expect(input).toHaveAttribute(
      'type',
      'text'
    )
  })

  /* =========================
     ACCESSIBILITY TESTS
  ========================= */

  test('password error message has alert role', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    await user.click(
      screen.getByTestId('password-submit')
    )

    expect(
      screen.getByRole('alert')
    ).toBeInTheDocument()
  })

  test('password strength uses status role', async () => {
    const user = userEvent.setup()

    render(<PasswordSection />)

    await user.type(
      screen.getByLabelText(/^new password$/i),
      'abc'
    )

    expect(
      screen.getByRole('status')
    ).toBeInTheDocument()
  })
})