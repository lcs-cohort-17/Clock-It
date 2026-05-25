import { useNavigate } from 'react-router-dom';
import { Clock } from 'lucide-react';
import { useAuth } from './useAuth';
import InternetStatus from './InternetStatus';
import PromoSection from './PromoSection';
import LoginForm from './LoginForm';
import type { User } from '../../types/auth';

type LoginPageProps = {
  onAuthenticated?: (user: User) => void;
};

export default function LoginPage({ onAuthenticated }: LoginPageProps) {
  const { connectionStatus, loading, error, login } = useAuth();
  const navigate = useNavigate();

  const handleLogin = async (credentials: any) => {
    try {
      const user = await login(credentials);
      onAuthenticated?.(user);
      // Redirect based on role
      if (user.role === 'admin') {
        navigate('/admin-dashboard'); // EDIT: change to your actual admin route
      } else {
        navigate('/staff-dashboard'); // EDIT: change to your actual staff route
      }
    } catch (err) {
      // error already handled by hook
    }
  };

  return (
    // EDIT: The overall background behind the right panel is white in the design
    <div className="flex min-h-screen bg-[#F5F5F5]">
      {/* Left Promo Column (hidden on mobile, visible from lg breakpoint as per tablet/desktop designs) */}
      <div className="hidden lg:flex lg:w-1/2 xl:w-2/5">
        <PromoSection />
      </div>

      {/* Right Column – centres the form and status */}
      <div className="flex w-full flex-col justify-center bg-white px-6 py-12 lg:w-1/2 xl:w-3/5 lg:px-16">
        {/* Top bar: Internet status and mobile logo (matches tablet/mobile header) */}
        <div className="mb-8 flex items-center justify-between">
          <div className="flex items-center gap-2 lg:hidden">
            {/* EDIT: If your logo differs, replace the placeholder */}
            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-[#093C5D] text-white">
              <Clock className="h-5 w-5" />
            </div>
            <span className="text-xl font-bold text-[#093C5D]">Clock It</span>
          </div>
          <InternetStatus status={connectionStatus} />
        </div>

        {/* Login Form Card – no extra card border, just the form */}
        <div className="mx-auto w-full max-w-md">
          <LoginForm onLogin={handleLogin} loading={loading} error={error} />
        </div>
      </div>
    </div>
  );
}