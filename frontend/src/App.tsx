
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import AdminDashboard from './pages/adminDashboard';
//import AttendanceLogPage from './pages/AttendanceLogPage';
//import QRCodesPage from './pages/QRCodesPage';
import UserManagement from './pages/UserManagement';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Navigate to="/admin" replace />} />
        <Route path="/admin" element={<AdminDashboard />} />
        {/* <Route path="/attendance" element={<AttendanceLogPage />} /> */}
        {/* <Route path="/qr-codes" element={<QRCodesPage />} /> */}
        <Route path="/users" element={<UserManagement />} />
         <Route path="/settings" element={<AdminDashboard />} />
        <Route path="*" element={<Navigate to="/admin" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
// END