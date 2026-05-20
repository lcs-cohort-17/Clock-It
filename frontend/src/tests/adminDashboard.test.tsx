import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import AdminDashboard from '../pages/adminDashboard';
import Sidebar from '../components/adminDashSidebar';
import TopNav from '../components/adminDashTopNav';

describe('Admin Dashboard Full Page', () => {
  it('renders the full admin dashboard layout', () => {
    render(<AdminDashboard />);

    // =====================
    // Sidebar
    // =====================

    expect(
      screen.getByText('Clock It')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Dashboard')
    ).toBeInTheDocument();

    expect(
      screen.getByText('QR Codes')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Attendance Logs')
    ).toBeInTheDocument();

    expect(
      screen.getByText('User Management')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Settings')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Log out')
    ).toBeInTheDocument();

    // =====================
    // Top Navigation
    // =====================

    expect(
      screen.getByText('Online')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Admin')
    ).toBeInTheDocument();

    // =====================
    // Dashboard Main Content
    // =====================

    expect(
      screen.getByText('Admin Dashboard')
    ).toBeInTheDocument();

    expect(
      screen.getByText(
        "Live overview of your team's attendance."
      )
    ).toBeInTheDocument();

    // =====================
    // Stats Cards
    // =====================

    expect(
      screen.getByText('Currently onsite')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Total clocked in today')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Pending sync')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Total events today')
    ).toBeInTheDocument();

    // =====================
    // Action Cards
    // =====================

    expect(
      screen.getByText('QR Generator')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Attendance Logs')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Settings')
    ).toBeInTheDocument();

    // =====================
    // Onsite Staff
    // =====================

    expect(
      screen.getByText('Sarah Mthembu')
    ).toBeInTheDocument();

    expect(
      screen.getByText('EMP001')
    ).toBeInTheDocument();

    // =====================
    // Recent Activity
    // =====================

    expect(
      screen.getByText('Recent Activity')
    ).toBeInTheDocument();

    expect(
      screen.getByText('View all >')
    ).toBeInTheDocument();

    // =====================
    // Sheets Integration
    // =====================

    expect(
      screen.getByText('Sheets Integration')
    ).toBeInTheDocument();

    expect(
      screen.getByText('Not connected')
    ).toBeInTheDocument();

    // =====================
    // Buttons
    // =====================

    expect(
      screen.getByRole('button', {
        name: /sync now/i,
      })
    ).toBeInTheDocument();

    expect(
      screen.getByRole('button', {
        name: /export to sheets/i,
      })
    ).toBeInTheDocument();
  });
});