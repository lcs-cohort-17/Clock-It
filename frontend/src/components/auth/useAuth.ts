import { useState, useEffect, useCallback } from 'react';
import type { ConnectionStatus, User, LoginCredentials } from '../../types/auth';
import { mockUsers } from '../../data/mockUsers';

export function useAuth() {
  const [connectionStatus, setConnectionStatus] = useState<ConnectionStatus>('checking');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [user, setUser] = useState<User | null>(() => {
    const storedUser = localStorage.getItem('authUser');
    return storedUser ? JSON.parse(storedUser) : null;
  });

  // Detect real internet connection
  useEffect(() => {
    const updateStatus = () => {
      setConnectionStatus(navigator.onLine ? 'online' : 'offline');
    };
    updateStatus();
    window.addEventListener('online', updateStatus);
    window.addEventListener('offline', updateStatus);
    return () => {
      window.removeEventListener('online', updateStatus);
      window.removeEventListener('offline', updateStatus);
    };
  }, []);

  const login = useCallback(async (credentials: LoginCredentials): Promise<User> => {
    setLoading(true);
    setError(null);

    // Simulate network delay (EDIT: adjust duration for testing)
    await new Promise(resolve => setTimeout(resolve, 1500));

    if (!navigator.onLine) {
      setLoading(false);
      throw new Error('No internet connection. Please check your network.');
    }

    try {
      let matchedUser: User | undefined;

      switch (credentials.method) {
        case 'email':
          matchedUser = mockUsers.find(
            u => u.email === credentials.email && u.password === credentials.password
          );
          break;
        case 'employeeId':
          matchedUser = mockUsers.find(
            u => u.employeeId === credentials.employeeId
          );
          break;
        case 'microsoft':
          // Simulate Microsoft SSO – match by microsoftEmail
          matchedUser = mockUsers.find(
            u => u.microsoftEmail === credentials.email
          );
          break;
        case 'google':
          // Simulate Google SSO – match by email (real OAuth would return email)
          matchedUser = mockUsers.find(
            u => u.email === credentials.email
          );
          break;
      }

      if (!matchedUser) {
        throw new Error('Invalid credentials. Please try again.');
      }

      localStorage.setItem('authUser', JSON.stringify(matchedUser));
      setUser(matchedUser);
      setLoading(false);
      return matchedUser;
    } catch (err: any) {
      setLoading(false);
      setError(err.message || 'Login failed');
      throw err;
    }
  }, []);

  const logout = useCallback(() => {
    localStorage.removeItem('authUser');
    localStorage.removeItem('loggedInUser');
    setUser(null);
  }, []);

  return { connectionStatus, loading, error, user, login, logout };
}
