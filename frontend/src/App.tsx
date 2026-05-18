import { useEffect, useState, type FormEvent } from 'react'
import { AuthLogo } from './components/auth/AuthLogo'
import { ConnectionStatus } from './components/auth/ConnectionStatus'
import { DemoAccounts, type DemoAccount } from './components/auth/DemoAccounts'
import { FormTextField } from './components/auth/FormTextField'
import { PromoPanel } from './components/auth/PromoPanel'

type LoginErrors = Partial<Record<'email' | 'password' | 'form', string>>

// Update this copy only after design approval; these strings render directly on the login page.
const LOGIN_COPY = {
  heading: 'Sign in',
  subheading: 'Use the credentials provided by your administrator.',
  forgotPasswordHref: '#forgot-password',
}

// Update these demo accounts when backend-approved test credentials change.
const DEMO_ACCOUNTS: DemoAccount[] = [
  { email: 'admin@clockit.app', password: 'admin123', role: 'Admin' },
  { email: 'sarah@clockit.app', password: 'sarah123', role: 'Staff' },
]

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
const LOGIN_DELAY_MS = 650

function useOnlineStatus() {
  const [isOnline, setIsOnline] = useState(() => navigator.onLine)

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
  const [email, setEmail] = useState(DEMO_ACCOUNTS[0].email)
  const [password, setPassword] = useState(DEMO_ACCOUNTS[0].password)
  const [rememberMe, setRememberMe] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [errors, setErrors] = useState<LoginErrors>({})

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
      const isApprovedCredential = DEMO_ACCOUNTS.some(
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

          <form noValidate onSubmit={handleSubmit}>
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
                  className="size-5 appearance-none rounded-full border border-[#0d4a6c] bg-transparent transition checked:border-[#0d4a6c] checked:bg-[#0d4a6c] focus:outline-none focus:ring-2 focus:ring-[#285f82]/20"
                  onChange={(event) => setRememberMe(event.target.checked)}
                  type="checkbox"
                />
                Remember me
              </label>
              <a
                className="text-[14px] font-medium text-[#003f64] hover:underline"
                href={LOGIN_COPY.forgotPasswordHref}
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
              {isSubmitting ? 'Signing in...' : 'Sign in'}
            </button>
          </form>

          <DemoAccounts accounts={DEMO_ACCOUNTS} />
        </div>
      </section>
    </main>
  )
}

export default App
