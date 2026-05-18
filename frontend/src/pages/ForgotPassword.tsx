import { useState } from 'react'
import { FormTextField } from '../components/auth/FormTextField'

type Props = {
  navigate: (path: string) => void
  onSendVerificationCode: (email: string) => void
  onSendResetLink: (email: string) => void
}

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

export default function ForgotPassword({ navigate, onSendVerificationCode, onSendResetLink }: Props) {
  const [email, setEmail] = useState('')
  const [error, setError] = useState<string | undefined>()
  const [status, setStatus] = useState<string | undefined>()

  const validate = (value: string) => EMAIL_PATTERN.test(value.trim())

  const handleSendLink = () => {
    setStatus(undefined)
    if (!validate(email)) {
      setError('Enter a valid email address.')
      return
    }
    setError(undefined)
    onSendResetLink(email.trim())
    setStatus('A reset link was generated (demo). Follow on-screen instructions.')
  }

  const handleSendCode = () => {
    setStatus(undefined)
    if (!validate(email)) {
      setError('Enter a valid email address.')
      return
    }
    setError(undefined)
    onSendVerificationCode(email.trim())
    setStatus('A verification code was sent (demo). Check the console for the code.')
  }

  return (
    <div className="w-full max-w-md rounded-[12px] border bg-white p-8">
      <h1 className="text-2xl font-bold">Reset your password</h1>
      <p className="mt-2 text-sm text-slate-600">Enter your email to receive a reset link or a verification code.</p>

      <FormTextField
        id="forgot-email"
        label="Email"
        type="email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        error={error}
        autoComplete="email"
      />

      <div className="mt-6 flex flex-col gap-3">
        <button
          className="h-[44px] rounded-md bg-[#0d4a6c] text-white font-semibold"
          onClick={handleSendLink}
        >
          Send reset link
        </button>

        <button
          className="h-[44px] rounded-md border border-[#0d4a6c] text-[#0d4a6c] font-semibold"
          onClick={handleSendCode}
        >
          Send verification code
        </button>
      </div>

      {status ? <p className="mt-4 text-sm text-green-700">{status}</p> : null}

      <div className="mt-6 text-sm">
        <a
          href="/"
          className="text-[#003f64] hover:underline"
          onClick={(e) => {
            e.preventDefault()
            navigate('/')
          }}
        >
          Back to sign in
        </a>
      </div>
    </div>
  )
}
