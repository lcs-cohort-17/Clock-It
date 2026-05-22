import { useState } from "react"
import Header from "./components/dashboard/Header"
import Sidebar from "./components/dashboard/Sidebar"
import DashboardGrid from "./components/dashboard/DashboardGrid"

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

function normalizeUser(user: User): UserProfileData {
  return {
    fullName: user.name,
    email: user.email,
    employeeId: user.employeeId,
    role: user.role,
  };
}

function getStoredUser() {
  const storedUser = localStorage.getItem('loggedInUser');
  return storedUser ? JSON.parse(storedUser) as UserProfileData : null;
}

// =================================================
// EXISTING DASHBOARD COMPONENT (completely unchanged)
// =================================================
function DashboardPage({ user }: { user: UserProfileData }) {
  const [isSidebarOpen, setIsSidebarOpen] = useState(false)

  return (
    <main className="min-h-screen bg-[#F5F5F5] dark:bg-[#081a2f] md:flex">
      {isSidebarOpen && (
        <div
          onClick={() => setIsSidebarOpen(false)}
          className="fixed inset-0 z-40 bg-black/30 md:hidden"
        />
      )}

      <Sidebar
        isOpen={isSidebarOpen}
        onClose={() => setIsSidebarOpen(false)}
        user={user}
      />

      <div className="flex flex-1 flex-col">
        <Header
          onMenuClick={() => setIsSidebarOpen(true)}
        />

        <DashboardGrid user={user} />
      </div>
    </main>
  )

}



// ============================
// NEW APP COMPONENT WITH ROUTING
// ============================
function App() {
  const [currentUser, setCurrentUser] = useState<UserProfileData | null>(() => getStoredUser());

  const handleAuthenticated = (user: User) => {
    const profileUser = normalizeUser(user);
    localStorage.setItem('loggedInUser', JSON.stringify(profileUser));
    setCurrentUser(profileUser);
  };

  const user = currentUser ?? fallbackUser;

  return (
    <BrowserRouter>
      <Routes>
  <Route path="/" element={<LoginPage onAuthenticated={handleAuthenticated} />} />

  <Route element={<DashboardPage user={user} />}>
    <Route path="/admin-dashboard" element={<DashboardGrid user={user} />} />
    <Route path="/staff-dashboard" element={<DashboardGrid user={user} />} />
    <Route path="/scan-qr" element={<ScanQRPage />} />
    <Route path="/history" element={<HistoryPage />} />
    <Route path="/calendar" element={<CalendarPage />} />
    <Route path="/profile" element={<UserProfilePage user={user} />} />
  </Route>

  <Route path="*" element={<Navigate to="/" replace />} />
</Routes>
    </BrowserRouter>
  );
}

// ============================
// DEFAULT EXPORT (now the App router)
// ============================
export default App;
