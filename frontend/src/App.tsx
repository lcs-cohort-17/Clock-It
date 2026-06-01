import { useState } from "react"
import Header from "./components/dashboard/Header"
import Sidebar from "./components/dashboard/Sidebar"
import DashboardGrid from "./components/dashboard/DashboardGrid"

// ============================
// NEW IMPORTS FOR ROUTING ----
// ============================
import { BrowserRouter, Routes, Route, Navigate, Outlet } from 'react-router-dom';
import LoginPage from './components/auth/LoginPage';
import ScanQRFlow from './components/Features/ScanQRFlow';
import { HistoryPage } from './pages/History-page';
import { UserProfilePage } from './pages/profile';
import type { UserProfileData } from './components/profile/UserProfileData';
import type { User } from './types/auth';

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
    <Route path="/scan-qr" element={<ScanQRFlow />} />
    <Route path="/history" element={<HistoryPage />} />
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
export default App;
>>>>>>> Gazelle/QA-team/testing
