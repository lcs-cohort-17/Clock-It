
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import AdminDashboard from './pages/adminDashboard';
import AttendanceLogPage from './pages/AttendanceLogPage';
import QRCodeGeneratorPage from './pages/QRCodeGeneratorPage';
import UserManagement from './pages/UserManagement';
import AdminSettingsPage from './pages/AdminSettingsPage';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Navigate to="/admin" replace />} />
        <Route path="/admin" element={<AdminDashboard />} />
        <Route path="/attendance" element={<AttendanceLogPage />} />
        <Route path="/qr-generator" element={<QRCodeGeneratorPage />} />
        <Route path="/users" element={<UserManagement />} />
        <Route path="/settings" element={<AdminSettingsPage />} />
        <Route path="*" element={<Navigate to="/admin" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
// END
