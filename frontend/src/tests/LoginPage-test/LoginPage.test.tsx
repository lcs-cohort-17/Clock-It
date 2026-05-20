import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import LoginPage from '../components/auth/LoginPage';

describe('LoginPage', () => {
  it('renders the sign in heading', () => {
    render(<MemoryRouter><LoginPage /></MemoryRouter>);
    expect(screen.getByText(/sign in/i)).toBeInTheDocument();
  });

  it('shows validation error on empty submit', async () => {
    render(<MemoryRouter><LoginPage /></MemoryRouter>);
    fireEvent.click(screen.getByRole('button', { name: /sign in/i }));
    // Wait for error display (mock useAuth may trigger an error)
    await waitFor(() => {
      expect(screen.getByText(/invalid credentials/i)).toBeInTheDocument();
    });
  });
});