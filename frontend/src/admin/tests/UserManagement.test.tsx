import { render, screen } from '@testing-library/react';
import UserManagement from '../pages/UserManagement';

describe('UserManagement', () => {
  it('renders the user management page', () => {
    render(<UserManagement />);

    expect(screen.getByText('User Management')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /add user/i })).toBeInTheDocument();
  });
});
