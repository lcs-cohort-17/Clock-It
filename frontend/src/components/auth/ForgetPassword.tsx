import { useState } from 'react';
import { X, Mail, CheckCircle, Loader2 } from 'lucide-react';
import mockUsers from '../../data/mockUsers.json';

interface Props {
  onClose: () => void;
  onLogin: (credentials: any) => void;
}

export default function ForgotPassword({ onClose, onLogin }: Props) {
  const [step, setStep] = useState<'enterEmail' | 'chooseAction' | 'enterCode'>('enterEmail');
  const [email, setEmail] = useState('');
  const [code, setCode] = useState('');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleContinue = () => {
    if (!email) {
      setError('Please enter your email.');
      return;
    }
    const userExists = mockUsers.some(u => u.email === email);
    if (!userExists) {
      setError('No account found with that email.');
      return;
    }
    setStep('chooseAction');
  };

  // Simulate sending a reset link
  const sendResetLink = async () => {
    setLoading(true);
    await new Promise(res => setTimeout(res, 1000));
    setMessage('If an account exists, a reset link has been sent.');
    setLoading(false);
  };

  // Simulate sending a verification code and then logging in
  const sendVerificationCode = async () => {
    setLoading(true);
    await new Promise(res => setTimeout(res, 1000));
    setStep('enterCode');
    setLoading(false);
  };

  const verifyCode = async () => {
    setLoading(true);
    // EDIT: In production, validate code from backend. Here we accept any 6-digit code for demo.
    if (code.length !== 6) {
      setError('Please enter a 6‑digit code.');
      setLoading(false);
      return;
    }
    // Simulate verification success
    const user = mockUsers.find(u => u.email === email);
    if (user) {
      // Log them in
      onLogin({ method: 'email', email: user.email, password: user.password }); // skip password for code login
      onClose();
    } else {
      setError('Verification failed.');
    }
    setLoading(false);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div className="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <button onClick={onClose} className="absolute right-4 top-4 text-gray-400 hover:text-gray-600">
          <X className="h-5 w-5" />
        </button>

        {step === 'enterEmail' && (
          <>
            <h3 className="text-lg font-semibold text-gray-900">Reset your password</h3>
            <p className="mt-1 text-sm text-gray-500">Enter your email and we’ll help you get back in.</p>
            <div className="mt-4">
              <label htmlFor="resetEmail" className="sr-only">Email</label>
              <div className="relative">
                <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  id="resetEmail"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="you@example.com"
                  className="block w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-4 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>
              {error && <p className="mt-2 text-sm text-red-500">{error}</p>}
            </div>
            <button
              onClick={handleContinue}
              className="mt-4 w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 transition"
            >
              Continue
            </button>
          </>
        )}

        {step === 'chooseAction' && (
          <>
            <h3 className="text-lg font-semibold text-gray-900">How would you like to proceed?</h3>
            <div className="mt-4 space-y-3">
              <button
                onClick={sendResetLink}
                disabled={loading}
                className="w-full rounded-lg border border-gray-300 bg-white py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center justify-center gap-2"
              >
                {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Mail className="h-4 w-4" />}
                Send reset link
              </button>
              <button
                onClick={sendVerificationCode}
                disabled={loading}
                className="w-full rounded-lg border border-gray-300 bg-white py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center justify-center gap-2"
              >
                {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : <CheckCircle className="h-4 w-4" />}
                Send verification code (login directly)
              </button>
            </div>
            {message && <p className="mt-3 text-sm text-green-600">{message}</p>}
          </>
        )}

        {step === 'enterCode' && (
          <>
            <h3 className="text-lg font-semibold text-gray-900">Enter verification code</h3>
            <p className="mt-1 text-sm text-gray-500">A 6‑digit code was sent to {email}</p>
            <div className="mt-4">
              <input
                type="text"
                maxLength={6}
                value={code}
                onChange={(e) => setCode(e.target.value.replace(/\D/g, ''))}
                placeholder="000000"
                className="block w-full rounded-lg border border-gray-300 py-2.5 px-4 text-center text-lg tracking-widest focus:border-indigo-500 focus:ring-indigo-500"
              />
              {error && <p className="mt-2 text-sm text-red-500">{error}</p>}
            </div>
            <button
              onClick={verifyCode}
              disabled={loading || code.length !== 6}
              className="mt-4 w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50 transition flex justify-center"
            >
              {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : 'Verify & Login'}
            </button>
          </>
        )}
      </div>
    </div>
  );
}