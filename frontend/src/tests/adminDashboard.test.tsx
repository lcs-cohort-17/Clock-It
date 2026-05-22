import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { describe, it, expect } from 'vitest';
import AdminDashboard from '../pages/adminDashboard';

describe('Admin Dashboard Full Page', () => {
  it('renders the full admin dashboard layout', async () => {
    render(
      <MemoryRouter>
        <AdminDashboard />
      </MemoryRouter>
    );

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
      screen.getAllByText('Attendance Logs').length
    ).toBeGreaterThan(0);

    expect(
      screen.getByText('User Management')
    ).toBeInTheDocument();

    expect(
      screen.getAllByText('Settings').length
    ).toBeGreaterThan(0);

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
      screen.getAllByText('Admin').length
    ).toBeGreaterThan(0);

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
      (await screen.findAllByText('Currently onsite', {}, { timeout: 2000 })).length
    ).toBeGreaterThan(0);

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
      screen.getAllByText('Attendance Logs').length
    ).toBeGreaterThan(0);

    expect(
      screen.getAllByText('Settings').length
    ).toBeGreaterThan(0);

    // =====================
    // Onsite Staff
    // =====================

    expect(
      screen.getAllByText('Sarah Mthembu').length
    ).toBeGreaterThan(0);

    expect(
      screen.getAllByText('EMP001').length
    ).toBeGreaterThan(0);

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
