import { useState } from 'react'

type Props = {
  navigate: (path: string) => void
  onVerifyCode: (email: string, code: string) => boolean
}

export default function VerifyCode({ navigate, onVerifyCode }: Props) {
  const params = new URLSearchParams(window.location.search)
  const email = params.get('email') || ''
  const [code, setCode] = useState('')
  const [error, setError] = useState<string | undefined>()

  const handleVerify = () => {
    setError(undefined)
    if (!email) {
      setError('Missing email address')
      return
    }
    if (!code.trim()) {
      setError('Enter the verification code')
      return
    }

    const ok = onVerifyCode(email, code.trim())
    if (ok) {
      navigate(`/reset-password?email=${encodeURIComponent(email)}`)
    } else {
      setError('Invalid or expired code')
    }
  }

  return (
    <div className="w-full max-w-md rounded-[12px] border bg-white p-8">
      <h1 className="text-2xl font-bold">Enter verification code</h1>
      <p className="mt-2 text-sm text-slate-600">We sent a verification code to <strong>{email}</strong>.</p>

      <label className="mt-6 block text-sm font-medium text-[#002b49]">Verification code</label>
      <input
        className="mt-2 h-[44px] w-full rounded-md border px-3"
        value={code}
        onChange={(e) => setCode(e.target.value)}
        inputMode="numeric"
      />

      {error ? <p className="mt-2 text-sm text-[#b42318]">{error}</p> : null}

      <div className="mt-6 flex gap-3">
        <button className="h-[44px] rounded-md bg-[#0d4a6c] text-white font-semibold" onClick={handleVerify}>
          Verify
        </button>
        <button
          className="h-[44px] rounded-md border border-slate-300 text-slate-700"
          onClick={(e) => {
            e.preventDefault()
            navigate('/forgot-password')
          }}
        >
          Back
        </button>
      </div>
    </div>
  )
}
