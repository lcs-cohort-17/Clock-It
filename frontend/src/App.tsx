<<<<<<< Updated upstream
import { useState } from "react"
import Header from "./components/dashboard/Header"
import Sidebar from "./components/dashboard/Sidebar"
import DashboardGrid from "./components/dashboard/DashboardGrid"
// fix
// ============================
// NEW IMPORTS FOR ROUTING ----
// ============================
import { BrowserRouter, Routes, Route, Navigate, Outlet } from 'react-router-dom';
import LoginPage from './components/auth/LoginPage';
import ScanQRPage from './pages/ScanQRPage';
import { HistoryPage } from './pages/History-page';
import { UserProfilePage } from './pages/profile';
import type { UserProfileData } from './components/profile/UserProfileData';
import type { User } from './types/auth';
import CalendarPage from './pages/CalendarPage';

const fallbackUser: UserProfileData = {
  fullName: 'Siba Mthembu',
  email: 'sarah@clockit.app',
  employeeId: 'STF-001',
  role: 'staff',
};

=======
import { useState } from 'react';
import type { ReactElement } from 'react';
import { BrowserRouter, Navigate, Outlet, Route, Routes } from 'react-router-dom';
import AdminLayout from './admin/components/AdminLayout';
import AdminSettingsPage from './admin/pages/AdminSettingsPage';
import AttendanceLogPage from './admin/pages/AttendanceLogPage';
import QRCodeGeneratorPage from './admin/pages/QRCodeGeneratorPage';
import UserManagement from './admin/pages/UserManagement';
import AdminDashboard from './admin/pages/adminDashboard';
import DashboardGrid from './staff/components/dashboard/DashboardGrid';
import Header from './staff/components/dashboard/Header';
import Sidebar from './staff/components/dashboard/Sidebar';
import LoginPage from './staff/components/auth/LoginPage';
import CalendarPage from './staff/pages/CalendarPage';
import { HistoryPage } from './staff/pages/History-page';
import ScanQRPage from './staff/pages/ScanQRPage';
import { UserProfilePage } from './staff/pages/profile';
import type { User } from './staff/types/auth';
import type { UserProfileData } from './staff/components/profile/UserProfileData';

>>>>>>> Stashed changes
function normalizeUser(user: User): UserProfileData {
  return {
    fullName: user.name,
    email: user.email,
    employeeId: user.employeeId,
    role: user.role,
  };
<<<<<<< Updated upstream
}

function getStoredUser() {
  const storedUser = localStorage.getItem('loggedInUser');
  return storedUser ? JSON.parse(storedUser) as UserProfileData : null;
}

function DashboardPage({ user }: { user: UserProfileData }) {
  const [isSidebarOpen, setIsSidebarOpen] = useState(false)

  return (
    <main className="h-screen overflow-hidden bg-[#F5F5F5] md:flex">
      {isSidebarOpen && (
        <div
          onClick={() => setIsSidebarOpen(false)}
          className="fixed inset-0 z-40 bg-black/40 md:hidden"
        />
      )}

      <Sidebar
        isOpen={isSidebarOpen}
        onClose={() => setIsSidebarOpen(false)}
        user={user}
      />

      <div className="flex h-screen flex-1 flex-col overflow-hidden">
        <Header onMenuClick={() => setIsSidebarOpen(true)} />

        <div className="flex-1 overflow-y-auto">
          <Outlet />
        </div>
      </div>
    </main>
  )
}

function App() {
  const [currentUser, setCurrentUser] = useState<UserProfileData | null>(() => getStoredUser());

  const handleAuthenticated = (user: User) => {
    const profileUser = normalizeUser(user);
    localStorage.setItem('loggedInUser', JSON.stringify(profileUser));
    setCurrentUser(profileUser);
  };

  const user = currentUser ?? fallbackUser;
=======
}

function getStoredUser(): User | null {
  const storedUser = localStorage.getItem('authUser');
  return storedUser ? (JSON.parse(storedUser) as User) : null;
}

function StaffLayout({ user }: { user: UserProfileData }) {
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);

  return (
    <main className="h-screen overflow-hidden bg-[#F5F5F5] md:flex">
      {isSidebarOpen && (
        <div
          onClick={() => setIsSidebarOpen(false)}
          className="fixed inset-0 z-40 bg-black/40 md:hidden"
        />
      )}

      <Sidebar isOpen={isSidebarOpen} onClose={() => setIsSidebarOpen(false)} user={user} />

      <div className="flex h-screen flex-1 flex-col overflow-hidden">
        <Header onMenuClick={() => setIsSidebarOpen(true)} />

        <div className="flex-1 overflow-y-auto">
          <Outlet />
        </div>
      </div>
    </main>
  );
}

function App() {
  const [currentUser, setCurrentUser] = useState<User | null>(() => getStoredUser());
  const profileUser = currentUser ? normalizeUser(currentUser) : null;

  const handleAuthenticated = (user: User) => {
    setCurrentUser(user);
  };

  const requireRole = (role: User['role'], element: ReactElement) => {
    if (!currentUser) {
      return <Navigate to="/" replace />;
    }

    if (currentUser.role !== role) {
      return <Navigate to={currentUser.role === 'admin' ? '/admin-dashboard' : '/staff-dashboard'} replace />;
    }

    return element;
  };
>>>>>>> Stashed changes

  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<LoginPage onAuthenticated={handleAuthenticated} />} />

<<<<<<< Updated upstream
        <Route element={<DashboardPage user={user} />}>
          <Route path="/admin-dashboard" element={<DashboardGrid user={user} />} />
          <Route path="/staff-dashboard" element={<DashboardGrid user={user} />} />
          <Route path="/scan-qr" element={<ScanQRPage />} />
          <Route path="/history" element={<HistoryPage />} />
          <Route path="/calendar" element={<CalendarPage />} />
          <Route path="/profile" element={<UserProfilePage user={user} />} />
        </Route>

=======
        <Route
          element={
            profileUser
              ? requireRole('staff', <StaffLayout user={profileUser} />)
              : <Navigate to="/" replace />
          }
        >
          <Route path="/staff-dashboard" element={<DashboardGrid user={profileUser!} />} />
          <Route path="/scan-qr" element={<ScanQRPage />} />
          <Route path="/history" element={<HistoryPage />} />
          <Route path="/calendar" element={<CalendarPage />} />
          <Route path="/profile" element={<UserProfilePage user={profileUser!} />} />
        </Route>

        <Route path="/admin-dashboard" element={requireRole('admin', <AdminLayout />)}>
          <Route index element={<AdminDashboard />} />
          <Route path="attendance" element={<AttendanceLogPage />} />
          <Route path="qr-generator" element={<QRCodeGeneratorPage />} />
          <Route path="users" element={<UserManagement />} />
          <Route path="settings" element={<AdminSettingsPage />} />
        </Route>

        <Route path="/admin" element={<Navigate to="/admin-dashboard" replace />} />
>>>>>>> Stashed changes
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
