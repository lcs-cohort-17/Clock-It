import { useEffect, useState, type FormEvent } from 'react'
import { AuthLogo } from './components/auth/AuthLogo'
import { ConnectionStatus } from './components/auth/ConnectionStatus'
import { DemoAccounts, type DemoAccount } from './components/auth/DemoAccounts'
import { FormTextField } from './components/auth/FormTextField'
import { PromoPanel } from './components/auth/PromoPanel'
import mockUsers from './test/mock_data/users.json'
import ForgotPassword from './pages/ForgotPassword'
import VerifyCode from './pages/VerifyCode'
import ResetPassword from './pages/ResetPassword'

type LoginErrors = Partial<Record<'email' | 'password' | 'form', string>>

// Update this copy only after design approval; these strings render directly on the login page.
const LOGIN_COPY = {
  heading: 'Sign in',
  subheading: 'Use the credentials provided by your administrator.',
}

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
const LOGIN_DELAY_MS = 650

// Start from the static mock users; allow demo-only overrides stored in localStorage
const initialAccounts = mockUsers as DemoAccount[]

function useOnlineStatus() {
  const [isOnline, setIsOnline] = useState(() => (typeof navigator !== 'undefined' ? navigator.onLine : true))

  useEffect(() => {
    const updateOnlineStatus = () => setIsOnline(navigator.onLine)

    window.addEventListener('online', updateOnlineStatus)
    window.addEventListener('offline', updateOnlineStatus)

    return () => {
      window.removeEventListener('online', updateOnlineStatus)
      window.removeEventListener('offline', updateOnlineStatus)
    }
  }, [])

  return isOnline
}

function validateLogin(email: string, password: string): LoginErrors {
  const errors: LoginErrors = {}

  if (!email.trim()) {
    errors.email = 'Email is required.'
  } else if (!EMAIL_PATTERN.test(email.trim())) {
    errors.email = 'Enter a valid email address.'
  }

  if (!password) {
    errors.password = 'Password is required.'
  }

  return errors
}

function App() {
  const isOnline = useOnlineStatus()

  const loadAccountOverrides = (): Record<string, string> => {
    try {
      return JSON.parse(localStorage.getItem('demo_accounts_overrides') || '{}')
    } catch {
      return {}
    }
  }

  const mergeAccounts = (): DemoAccount[] => {
    const overrides = loadAccountOverrides()
    return initialAccounts.map((a) => ({ ...a, password: overrides[a.email] ?? a.password }))
  }

  const [demoAccounts, setDemoAccounts] = useState<DemoAccount[]>(() => mergeAccounts())
  const [email, setEmail] = useState(demoAccounts[0]?.email ?? '')
  const [password, setPassword] = useState(demoAccounts[0]?.password ?? '')
  const [rememberMe, setRememberMe] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [errors, setErrors] = useState<LoginErrors>({})

  const getLocation = () => window.location.pathname + window.location.search
  const [route, setRoute] = useState(getLocation)

  useEffect(() => {
    const onPop = () => setRoute(getLocation())
    window.addEventListener('popstate', onPop)
    return () => window.removeEventListener('popstate', onPop)
  }, [])

  const navigate = (path: string) => {
    if (path !== getLocation()) {
      window.history.pushState({}, '', path)
      setRoute(getLocation())
    }
  }

  const saveAccountOverride = (emailToSave: string, newPassword: string) => {
    const overrides = loadAccountOverrides()
    overrides[emailToSave] = newPassword
    localStorage.setItem('demo_accounts_overrides', JSON.stringify(overrides))
    setDemoAccounts(mergeAccounts())
  }

  const sendVerificationCode = (targetEmail: string) => {
    const code = String(Math.floor(100000 + Math.random() * 900000))
    const expiresAt = Date.now() + 10 * 60 * 1000 // 10 minutes
    sessionStorage.setItem(`verification_code:${targetEmail}`, JSON.stringify({ code, expiresAt }))
    // Simulate send — for demo only
    // eslint-disable-next-line no-console
    console.log(`Verification code for ${targetEmail}: ${code}`)
    navigate(`/verify-code?email=${encodeURIComponent(targetEmail)}`)
  }

  const verifyCode = (targetEmail: string, code: string) => {
    const raw = sessionStorage.getItem(`verification_code:${targetEmail}`)
    if (!raw) return false
    try {
      const { code: realCode, expiresAt } = JSON.parse(raw)
      if (Date.now() > expiresAt) return false
      return code === realCode
    } catch {
      return false
    }
  }

  const sendResetLink = (targetEmail: string) => {
    const token = Math.random().toString(36).slice(2)
    const expiresAt = Date.now() + 60 * 60 * 1000 // 1 hour
    sessionStorage.setItem(`reset_token:${token}`, JSON.stringify({ email: targetEmail, expiresAt }))
    // In a real app this would be emailed. For demo we navigate directly.
    // eslint-disable-next-line no-console
    console.log(`Reset link for ${targetEmail}: /reset-password?token=${token}`)
    navigate(`/reset-password?token=${token}`)
  }

  const resetPasswordByToken = (token: string, newPassword: string) => {
    const raw = sessionStorage.getItem(`reset_token:${token}`)
    if (!raw) return false
    try {
      const { email: targetEmail, expiresAt } = JSON.parse(raw)
      if (Date.now() > expiresAt) return false
      saveAccountOverride(targetEmail, newPassword)
      sessionStorage.removeItem(`reset_token:${token}`)
      return true
    } catch {
      return false
    }
  }

  const resetPasswordByEmail = (targetEmail: string, newPassword: string) => {
    saveAccountOverride(targetEmail, newPassword)
    return true
  }

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    const validationErrors = validateLogin(email, password)
    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors)
      return
    }

    setErrors({})
    setIsSubmitting(true)

    window.setTimeout(() => {
      const isApprovedCredential = demoAccounts.some(
        (account) => account.email === email.trim() && account.password === password,
      )

      setIsSubmitting(false)

      if (!isApprovedCredential) {
        setErrors({
          form: 'Incorrect email or password. Please check your Clock It credentials.',
        })
      }
    }, LOGIN_DELAY_MS)
  }

  // Route rendering
  if (route.startsWith('/forgot-password')) {
    return (
      <main className="min-h-screen bg-[#f7f7f7] text-[#002b49] flex items-center justify-center">
        <ForgotPassword
          navigate={navigate}
          onSendVerificationCode={sendVerificationCode}
          onSendResetLink={sendResetLink}
        />
      </main>
    )
  }

  if (route.startsWith('/verify-code')) {
    return (
      <main className="min-h-screen bg-[#f7f7f7] text-[#002b49] flex items-center justify-center">
        <VerifyCode navigate={navigate} onVerifyCode={verifyCode} />
      </main>
    )
  }

  if (route.startsWith('/reset-password')) {
    return (
      <main className="min-h-screen bg-[#f7f7f7] text-[#002b49] flex items-center justify-center">
        <ResetPassword
          navigate={navigate}
          onResetPasswordByToken={resetPasswordByToken}
          onResetPasswordByEmail={resetPasswordByEmail}
        />
      </main>
    )
  }

  return (
    <main className="min-h-screen bg-[#f7f7f7] text-[#002b49] min-[1200px]:grid min-[1200px]:grid-cols-2">
      <PromoPanel />

      <section className="relative flex min-h-screen justify-center px-6 py-7 sm:px-8 min-[1200px]:items-center">
        <span aria-hidden="true" className="absolute right-7 top-7 text-[#285f82]">
          <svg className="size-5" viewBox="0 0 24 24" fill="none">
            <path
              d="M20 14.2A7.2 7.2 0 0 1 9.8 4 8.2 8.2 0 1 0 20 14.2Z"
              stroke="currentColor"
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth="2"
            />
          </svg>
        </span>

        <div className="w-full max-w-[480px]">
          <div className="mb-10 flex justify-center min-[1200px]:hidden">
            <AuthLogo />
          </div>

          <form noValidate onSubmit={handleSubmit} aria-busy={isSubmitting}>
            <h1 className="text-[31px] font-bold leading-tight text-[#002b49]">
              {LOGIN_COPY.heading}
            </h1>
            <p className="mt-2 text-[18px] leading-tight text-[#2d5770]">{LOGIN_COPY.subheading}</p>

            <ConnectionStatus isOnline={isOnline} />

            <FormTextField
              autoComplete="email"
              error={errors.email}
              id="email"
              inputMode="email"
              label="Email"
              name="email"
              disabled={isSubmitting}
              onChange={(event) => {
                setEmail(event.target.value)
                setErrors((currentErrors) => ({ ...currentErrors, email: undefined, form: undefined }))
              }}
              type="email"
              value={email}
            />

            <FormTextField
              autoComplete="current-password"
              error={errors.password}
              id="password"
              label="Password"
              name="password"
              disabled={isSubmitting}
              onChange={(event) => {
                setPassword(event.target.value)
                setErrors((currentErrors) => ({
                  ...currentErrors,
                  password: undefined,
                  form: undefined,
                }))
              }}
              type="password"
              value={password}
            />

            <div className="mt-5 flex items-center justify-between gap-4">
                <label className="flex cursor-pointer items-center gap-3 text-[18px] text-[#002b49]">
                <input
                  checked={rememberMe}
                  disabled={isSubmitting}
                  className="size-5 appearance-none rounded-full border border-[#0d4a6c] bg-transparent transition checked:border-[#0d4a6c] checked:bg-[#0d4a6c] focus:outline-none focus:ring-2 focus:ring-[#285f82]/20"
                  onChange={(event) => setRememberMe(event.target.checked)}
                  type="checkbox"
                />
                Remember me
              </label>
              <a
                className="text-[14px] font-medium text-[#003f64] hover:underline"
                href="/forgot-password"
                onClick={(e) => {
                  e.preventDefault()
                  if (isSubmitting) return
                  navigate('/forgot-password')
                }}
                aria-disabled={isSubmitting}
              >
                Forgot password?
              </a>
            </div>

            {errors.form ? (
              <p className="mt-4 text-sm font-medium text-[#b42318]" role="alert">
                {errors.form}
              </p>
            ) : null}

            <button
              className="mt-[18px] h-[51px] w-full rounded-[10px] bg-[#0d4a6c] text-[18px] font-bold text-white transition hover:bg-[#0a3f5d] disabled:cursor-not-allowed disabled:opacity-75"
              disabled={isSubmitting}
              type="submit"
            >
              {isSubmitting ? (
                <span className="flex items-center justify-center gap-2">
                  <svg className="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                  </svg>
                  <span>Signing in...</span>
                </span>
              ) : (
                'Sign in'
              )}
            </button>
          </form>

          <DemoAccounts accounts={demoAccounts} />
        </div>
      </section>
    </main>
  )
}

export default App
