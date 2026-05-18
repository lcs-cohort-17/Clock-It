import { useState } from 'react'

type Props = {
  navigate: (path: string) => void
  onResetPasswordByToken: (token: string, newPassword: string) => boolean
  onResetPasswordByEmail: (email: string, newPassword: string) => boolean
}

export default function ResetPassword({ navigate, onResetPasswordByToken, onResetPasswordByEmail }: Props) {
  const params = new URLSearchParams(window.location.search)
  const token = params.get('token')
  const email = params.get('email')

  const [password, setPassword] = useState('')
  const [confirm, setConfirm] = useState('')
  const [error, setError] = useState<string | undefined>()
  const [success, setSuccess] = useState<string | undefined>()

  const validate = () => {
    if (!password) return 'Password is required.'
    if (password.length < 6) return 'Password must be at least 6 characters.'
    if (password !== confirm) return 'Passwords do not match.'
    return undefined
  }

  const handleReset = () => {
    setError(undefined)
    setSuccess(undefined)
    const v = validate()
    if (v) {
      setError(v)
      return
    }

    let ok = false
    if (token) {
      ok = onResetPasswordByToken(token, password)
    } else if (email) {
      ok = onResetPasswordByEmail(email, password)
    }

    if (ok) {
      setSuccess('Password updated (demo). You can now sign in.')
      setTimeout(() => navigate('/'), 1200)
    } else {
      setError('Unable to reset password. The link or code may be invalid or expired.')
    }
  }

  return (
    <div className="w-full max-w-md rounded-[12px] border bg-white p-8">
      <h1 className="text-2xl font-bold">Choose a new password</h1>
      <p className="mt-2 text-sm text-slate-600">{token ? 'Reset via link' : email ? `Reset for ${email}` : 'Reset password'}</p>

      <label className="mt-6 block text-sm font-medium text-[#002b49]">New password</label>
      <input className="mt-2 h-[44px] w-full rounded-md border px-3" value={password} onChange={(e) => setPassword(e.target.value)} />

      <label className="mt-4 block text-sm font-medium text-[#002b49]">Confirm password</label>
      <input className="mt-2 h-[44px] w-full rounded-md border px-3" value={confirm} onChange={(e) => setConfirm(e.target.value)} />

      {error ? <p className="mt-2 text-sm text-[#b42318]">{error}</p> : null}
      {success ? <p className="mt-2 text-sm text-green-700">{success}</p> : null}

      <div className="mt-6 flex gap-3">
        <button className="h-[44px] rounded-md bg-[#0d4a6c] text-white font-semibold" onClick={handleReset}>
          Set password
        </button>
        <button
          className="h-[44px] rounded-md border border-slate-300 text-slate-700"
          onClick={(e) => {
            e.preventDefault()
            navigate('/')
          }}
        >
          Cancel
        </button>
      </div>
    </div>
  )
}
