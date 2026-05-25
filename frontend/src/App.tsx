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

import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import AdminLayout from './components/AdminLayout';
import AdminDashboard from './pages/adminDashboard';
import AttendanceLogPage from './pages/AttendanceLogPage';
import QRCodeGeneratorPage from './pages/QRCodeGeneratorPage';
import UserManagement from './pages/UserManagement';
import AdminSettingsPage from './pages/AdminSettingsPage';

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
        <Route path="/" element={<Navigate to="/admin" replace />} />
        <Route element={<AdminLayout />}>
          <Route path="/admin" element={<AdminDashboard />} />
          <Route path="/attendance" element={<AttendanceLogPage />} />
          <Route path="/qr-generator" element={<QRCodeGeneratorPage />} />
          <Route path="/users" element={<UserManagement />} />
          <Route path="/settings" element={<AdminSettingsPage />} />
        </Route>
        <Route path="*" element={<Navigate to="/admin" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;