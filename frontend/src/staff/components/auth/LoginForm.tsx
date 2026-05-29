import { useState } from 'react';
import { Mail, Lock, Key, Eye, EyeOff, Loader2 } from 'lucide-react';
import type { LoginMethod } from '../../types/auth';
import ForgotPassword from './ForgotPassword';
import DemoAccounts from './DemoAccounts';

interface Props {
  onLogin: (credentials: any) => void;
  loading: boolean;
  error: string | null;
}

export default function LoginForm({ onLogin, loading, error }: Props) {
  const [loginMethod, setLoginMethod] = useState<LoginMethod>('email');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [employeeId, setEmployeeId] = useState('');
  const [rememberMe, setRememberMe] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [showForgotPassword, setShowForgotPassword] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (loginMethod === 'email' && (!email || !password)) {
      return;
    }
    onLogin({
      method: loginMethod,
      email: loginMethod === 'employeeId' ? undefined : email,
      password: loginMethod === 'email' ? password : undefined,
      employeeId: loginMethod === 'employeeId' ? employeeId : undefined,
    });
  };

  const handleMicrosoftLogin = () => {
    // EDIT: Replace with real MSAL later; fallback email matches admin for demo
    onLogin({ method: 'microsoft', email: email || 'taaraa@clockit.com' });
  };

  const handleGoogleLogin = () => {
    onLogin({ method: 'google', email: email || 'shaheed@clockit.com' });
  };

  return (
    <>
      <div className="w-full max-w-md mx-auto">
        {/* Heading – matching the larger font and spacing */}
        <div className="mb-8">
          <h2 className="text-3xl font-bold tracking-tight text-[#093C5D] dark:text-[#eff6ff]">Sign in to Clock It</h2>
          <p className="mt-2 text-base text-[#3B7597] dark:text-[#cbd5ff]">Welcome back! Please enter your details.</p>
        </div>

        {/* Login Method Tabs – pill style as seen in the design */}
        <div className="flex rounded-lg bg-[#F5F5F5] p-1 mb-8 dark:bg-[#081a2f]">
          {(['email', 'employeeId'] as LoginMethod[]).map((method) => (
            <button
              key={method}
              onClick={() => setLoginMethod(method)}
              className={`flex-1 rounded-md py-2.5 text-sm font-medium transition ${
                loginMethod === method
                  ? 'bg-white shadow text-[#093C5D] dark:bg-[#164068] dark:text-[#eff6ff]'
                  : 'text-[#3B7597] hover:text-[#093C5D] dark:text-[#cbd5ff] dark:hover:text-white'
              }`}
            >
              {method === 'email' ? 'Email' : 'Employee ID'}
            </button>
          ))}
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {loginMethod === 'email' && (
            <>
              <div>
                <label htmlFor="email" className="block text-sm font-medium text-[#093C5D] mb-1 dark:text-[#eff6ff]">
                  Email
                </label>
                <div className="relative">
                  <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-[#3B7597] dark:text-[#cbd5ff]" />
                  <input
                    id="email"
                    type="email"
                    required
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="you@example.com"
                    className="block w-full rounded-lg border border-[#3B7597]/30 py-3 pl-11 pr-4 text-sm text-[#093C5D] outline-none placeholder:text-slate-400 focus:border-[#3B7597] focus:ring-2 focus:ring-[#3B7597]/20 dark:border-[#23456f] dark:bg-[#081a2f] dark:text-[#eff6ff] dark:placeholder:text-[#9bb3d1]"
                  />
                </div>
              </div>

              <div>
                <label htmlFor="password" className="block text-sm font-medium text-[#093C5D] mb-1 dark:text-[#eff6ff]">
                  Password
                </label>
                <div className="relative">
                  <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-[#3B7597] dark:text-[#cbd5ff]" />
                  <input
                    id="password"
                    type={showPassword ? 'text' : 'password'}
                    required
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Enter your password"
                    className="block w-full rounded-lg border border-[#3B7597]/30 py-3 pl-11 pr-11 text-sm text-[#093C5D] outline-none placeholder:text-slate-400 focus:border-[#3B7597] focus:ring-2 focus:ring-[#3B7597]/20 dark:border-[#23456f] dark:bg-[#081a2f] dark:text-[#eff6ff] dark:placeholder:text-[#9bb3d1]"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-1/2 -translate-y-1/2"
                  >
                    {showPassword ? (
                      <EyeOff className="h-5 w-5 text-[#3B7597] dark:text-[#cbd5ff]" />
                    ) : (
                      <Eye className="h-5 w-5 text-[#3B7597] dark:text-[#cbd5ff]" />
                    )}
                  </button>
                </div>
              </div>

              <div className="flex items-center justify-between">
                <label className="flex items-center gap-2 text-sm text-[#3B7597] dark:text-[#cbd5ff]">
                  <input
                    type="checkbox"
                    checked={rememberMe}
                    onChange={(e) => setRememberMe(e.target.checked)}
                    className="h-4 w-4 rounded border-[#3B7597]/30 text-[#9CB07A] focus:ring-[#9CB07A]"
                  />
                  Remember me
                </label>
                <button
                  type="button"
                  onClick={() => setShowForgotPassword(true)}
                  className="text-sm font-medium text-[#093C5D] hover:text-[#3B7597] dark:text-[#eff6ff] dark:hover:text-[#cbd5ff]"
                >
                  Forgot password?
                </button>
              </div>
            </>
          )}

          {loginMethod === 'employeeId' && (
            <div>
              <label htmlFor="employeeId" className="block text-sm font-medium text-[#093C5D] mb-1 dark:text-[#eff6ff]">
                Employee ID
              </label>
              <div className="relative">
                <Key className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-[#3B7597] dark:text-[#cbd5ff]" />
                <input
                  id="employeeId"
                  type="text"
                  required
                  value={employeeId}
                  onChange={(e) => setEmployeeId(e.target.value)}
                  placeholder="e.g. EMP001"
                  className="block w-full rounded-lg border border-[#3B7597]/30 py-3 pl-11 pr-4 text-sm text-[#093C5D] outline-none placeholder:text-slate-400 focus:border-[#3B7597] focus:ring-2 focus:ring-[#3B7597]/20 dark:border-[#23456f] dark:bg-[#081a2f] dark:text-[#eff6ff] dark:placeholder:text-[#9bb3d1]"
                />
              </div>
            </div>
          )}

          {error && (
            <div className="rounded-lg bg-red-50 p-3 text-sm text-red-600 dark:bg-red-950/50 dark:text-red-200">{error}</div>
          )}

          <button
            type="submit"
            disabled={loading}
            className="w-full rounded-lg bg-[#093C5D] py-3 text-sm font-semibold text-white shadow hover:bg-[#3B7597] focus:outline-none focus:ring-2 focus:ring-[#3B7597] focus:ring-offset-2 disabled:opacity-50 transition"
          >
            {loading ? (
              <span className="flex items-center justify-center gap-2">
                <Loader2 className="h-5 w-5 animate-spin" />
                Signing in...
              </span>
            ) : (
              'Sign in'
            )}
          </button>
        </form>

        {/* OR Continue With – matches the design: divider with "Or continue with" and two buttons */}
        <div className="mt-8">
          <div className="relative">
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t border-[#3B7597]/20" />
            </div>
            <div className="relative flex justify-center text-sm">
              <span className="bg-white px-3 text-[#3B7597] dark:bg-[#0b2142] dark:text-[#cbd5ff]">Or continue with</span>
            </div>
          </div>
          <div className="mt-5 grid grid-cols-2 gap-4">
            <button
              onClick={handleMicrosoftLogin}
              className="flex items-center justify-center gap-2 rounded-lg border border-[#3B7597]/30 bg-white py-3 text-sm font-medium text-[#093C5D] hover:bg-[#F5F5F5] transition dark:border-[#23456f] dark:bg-[#081a2f] dark:text-[#eff6ff] dark:hover:bg-[#103553]"
            >
              {/* Microsoft icon */}
              <svg className="h-5 w-5 shrink-0" viewBox="0 0 21 21" fill="none" aria-hidden="true">
                <rect x="1" y="1" width="9" height="9" fill="#F25022" />
                <rect x="11" y="1" width="9" height="9" fill="#7FBA00" />
                <rect x="1" y="11" width="9" height="9" fill="#00A4EF" />
                <rect x="11" y="11" width="9" height="9" fill="#FFB900" />
              </svg>
              Microsoft
            </button>
            <button
              onClick={handleGoogleLogin}
              className="flex items-center justify-center gap-2 rounded-lg border border-[#3B7597]/30 bg-white py-3 text-sm font-medium text-[#093C5D] hover:bg-[#F5F5F5] transition dark:border-[#23456f] dark:bg-[#081a2f] dark:text-[#eff6ff] dark:hover:bg-[#103553]"
            >
              <svg className="h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M21.6 12.23c0-.78-.07-1.53-.2-2.23H12v4.22h5.38a4.6 4.6 0 0 1-1.99 3.02v2.51h3.23c1.89-1.74 2.98-4.3 2.98-7.52Z" />
                <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.62-2.43l-3.23-2.51c-.9.6-2.04.95-3.39.95-2.6 0-4.8-1.76-5.59-4.12H3.08v2.59A9.99 9.99 0 0 0 12 22Z" />
                <path fill="#FBBC05" d="M6.41 13.89a6.01 6.01 0 0 1 0-3.78V7.52H3.08a10 10 0 0 0 0 8.96l3.33-2.59Z" />
                <path fill="#EA4335" d="M12 5.98c1.47 0 2.79.5 3.82 1.49l2.87-2.87C16.95 2.98 14.7 2 12 2a9.99 9.99 0 0 0-8.92 5.52l3.33 2.59C7.2 7.74 9.4 5.98 12 5.98Z" />
              </svg>
              Google
            </button>
          </div>
        </div>

        {/* Demo Accounts – shown below the alternative login buttons */}
        <div className="mt-8">
          <DemoAccounts />
        </div>
      </div>

      {showForgotPassword && (
        <ForgotPassword
          onClose={() => setShowForgotPassword(false)}
          onLogin={onLogin}
        />
      )}
    </>
  );
}
