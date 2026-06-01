export interface User {
  id: string;
  name: string;
  email: string;
  password: string;
  role: 'admin' | 'staff';
  employeeId: string;
  microsoftEmail?: string; // optional if using MS login
}

export type LoginMethod = 'email' | 'employeeId' | 'microsoft' | 'google';

export interface LoginCredentials {
  method: LoginMethod;
  email?: string;
  password?: string;
  employeeId?: string;
}

export type ConnectionStatus = 'online' | 'offline' | 'checking';