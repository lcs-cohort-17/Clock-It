import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import {HistoryPage} from '../../pages/History-page';

describe('HistoryPage', () => {
  it('renders the history page heading', () => {
    render(<HistoryPage />);

    expect(
      screen.getByText(/Attendance History/i)
    ).toBeInTheDocument();
  });

  it('renders summary cards', () => {
    render(<HistoryPage />);

    expect(screen.getByText(/Total Records/i)).toBeInTheDocument();
    expect(screen.getByText(/Present/i)).toBeInTheDocument();
    expect(screen.getByText(/Late/i)).toBeInTheDocument();
    expect(screen.getByText(/Absent/i)).toBeInTheDocument();
    expect(screen.getByText(/Avg Hours/i)).toBeInTheDocument();
  });

  it('renders attendance table data', () => {
    render(<HistoryPage />);

    expect(screen.getByText(/Employee/i)).toBeInTheDocument();
    expect(screen.getByText(/Status/i)).toBeInTheDocument();
  });
});