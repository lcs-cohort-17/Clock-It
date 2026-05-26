import '@testing-library/jest-dom/vitest';
import { act, fireEvent, render, renderHook, screen, within } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import QRDropdownButton from '../components/qr/QRDropdownButton';
import QRModal from '../components/qr/QRModal';
import { useQRCode } from '../components/hooks/useQRCode';
import QRCodeGeneratorPage from '../pages/QRCodeGeneratorPage';

vi.mock('qrcode.react', () => ({
  QRCodeCanvas: ({
    value,
    size,
    level,
    includeMargin,
    fgColor,
    bgColor,
    className,
  }: {
    value: string;
    size: number;
    level: string;
    includeMargin: boolean;
    fgColor?: string;
    bgColor?: string;
    className?: string;
  }) => (
    <div
      aria-label="Generated QR code"
      className={className}
      data-bg-color={bgColor}
      data-fg-color={fgColor}
      data-include-margin={String(includeMargin)}
      data-level={level}
      data-size={String(size)}
      data-testid="qr-code"
      data-value={value}
    />
  ),
}));

const advancePastGeneration = async () => {
  await act(async () => {
    vi.advanceTimersByTime(800);
  });
};

describe('QR code generator requirements', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2026, 4, 26, 8, 4));
    vi.spyOn(Math, 'random').mockReturnValue(0.5);
  });

  afterEach(() => {
    vi.clearAllTimers();
    vi.useRealTimers();
    vi.restoreAllMocks();
  });

  it('shows the dropdown button with Clock In QR and Clock Out QR options', () => {
    const onSelect = vi.fn();

    render(<QRDropdownButton onSelect={onSelect} />);

    const dropdownButton = screen.getByRole('button', { name: /open qr code options/i });
    expect(dropdownButton).toBeInTheDocument();
    expect(dropdownButton).toHaveAttribute('aria-haspopup', 'true');
    expect(dropdownButton).toHaveAttribute('aria-expanded', 'false');

    fireEvent.click(dropdownButton);

    expect(dropdownButton).toHaveAttribute('aria-expanded', 'true');
    expect(screen.getByRole('menu')).toBeInTheDocument();
    expect(screen.getByRole('menuitem', { name: /clock in qr/i })).toBeInTheDocument();
    expect(screen.getByRole('menuitem', { name: /clock out qr/i })).toBeInTheDocument();
  });

  it('opens the same modal with the correct QR type from the generator page', async () => {
    const { unmount } = render(<QRCodeGeneratorPage />);

    fireEvent.click(screen.getByRole('button', { name: /open qr code options/i }));
    fireEvent.click(screen.getByRole('menuitem', { name: /clock in qr/i }));
    expect(screen.getByRole('dialog')).toHaveTextContent('Clock In QR Code');

    await advancePastGeneration();
    expect(screen.getByTestId('qr-code')).toHaveAttribute(
      'data-value',
      'Clocked in at 2026/05/26 08:04',
    );
    expect(screen.getByTestId('qr-code')).toHaveAttribute('data-fg-color', '#002f4f');
    expect(within(screen.getByRole('dialog')).queryByText(/^Active$/)).not.toBeInTheDocument();

    fireEvent.click(screen.getByRole('button', { name: /close modal/i }));
    expect(screen.queryByRole('dialog')).not.toBeInTheDocument();

    unmount();
    render(<QRCodeGeneratorPage />);

    fireEvent.click(screen.getByRole('button', { name: /open qr code options/i }));
    fireEvent.click(screen.getByRole('menuitem', { name: /clock out qr/i }));
    expect(screen.getByRole('dialog')).toHaveTextContent('Clock Out QR Code');

    await advancePastGeneration();
    expect(screen.getByTestId('qr-code')).toHaveAttribute(
      'data-value',
      'Clocked out at 2026/05/26 08:04',
    );
  });

  it('renders a responsive and accessible modal shell', async () => {
    render(<QRModal isOpen onClose={vi.fn()} qrType="clock-in" />);

    const dialog = screen.getByRole('dialog');
    expect(dialog).toHaveAttribute('aria-modal', 'true');
    expect(dialog).toHaveAttribute('aria-labelledby', 'qr-modal-title');
    expect(screen.getByRole('button', { name: /close modal/i })).toBeInTheDocument();

    const modalPanel = dialog.querySelector('.max-w-md');
    expect(modalPanel).toHaveClass('w-full');
    expect(modalPanel).toHaveClass('max-w-md');

    await advancePastGeneration();
    expect(screen.getByTestId('qr-code')).toHaveAttribute('data-size', '200');
    expect(screen.getByTestId('qr-code')).toHaveAttribute('data-level', 'H');
    expect(screen.getByTestId('qr-code')).toHaveAttribute('data-include-margin', 'true');
    expect(screen.getByTestId('qr-code')).toHaveAttribute('data-bg-color', '#ffffff');
  });

  it('closes properly and resets state when reopened', async () => {
    const onClose = vi.fn();
    const { rerender } = render(<QRModal isOpen onClose={onClose} qrType="clock-in" />);

    await advancePastGeneration();
    expect(screen.getByTestId('qr-code')).toHaveAttribute(
      'data-value',
      'Clocked in at 2026/05/26 08:04',
    );

    fireEvent.click(screen.getByRole('button', { name: /close modal/i }));
    expect(onClose).toHaveBeenCalledTimes(1);

    rerender(<QRModal isOpen={false} onClose={onClose} qrType="clock-in" />);
    expect(screen.queryByRole('dialog')).not.toBeInTheDocument();

    rerender(<QRModal isOpen onClose={onClose} qrType="clock-out" />);
    expect(screen.getByText(/generating qr code/i)).toBeInTheDocument();

    await advancePastGeneration();
    expect(screen.getByTestId('qr-code')).toHaveAttribute(
      'data-value',
      'Clocked out at 2026/05/26 08:04',
    );
  });

  it('shows error feedback when QR generation fails', async () => {
    vi.mocked(Math.random).mockReturnValue(0.05);

    render(<QRModal isOpen onClose={vi.fn()} qrType="clock-in" />);

    await advancePastGeneration();

    expect(screen.getByText(/generation failed/i)).toBeInTheDocument();
    expect(screen.getByText(/network error: failed to generate qr token/i)).toBeInTheDocument();
    expect(screen.queryByTestId('qr-code')).not.toBeInTheDocument();
  });

  it('expires active QR codes after the short fallback window and shows visual feedback', async () => {
    render(<QRModal isOpen onClose={vi.fn()} qrType="clock-in" />);

    await advancePastGeneration();
    expect(screen.getByText(/ready to scan/i)).toBeInTheDocument();
    expect(screen.getByText(/expires in 60 seconds/i)).toBeInTheDocument();

    await act(async () => {
      vi.advanceTimersByTime(60_000);
    });

    expect(screen.getByText(/qr code expired/i)).toBeInTheDocument();
    expect(screen.getByText(/please refresh to get a new one/i)).toBeInTheDocument();
    expect(screen.queryByTestId('qr-code')).not.toBeInTheDocument();
  });
});

describe('useQRCode single-use and refresh behavior', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.spyOn(Math, 'random').mockReturnValue(0.5);
  });

  afterEach(() => {
    vi.clearAllTimers();
    vi.useRealTimers();
    vi.restoreAllMocks();
  });

  it('marks a QR code as used so it cannot remain active after the first scan', async () => {
    const { result } = renderHook(() => useQRCode());

    act(() => {
      void result.current.generateQR('clock-in');
    });
    await advancePastGeneration();

    expect(result.current.status).toBe('active');

    act(() => {
      result.current.markAsUsed();
    });

    expect(result.current.status).toBe('used');
  });

  it('refreshing generates a new unique token and resets the QR to active', async () => {
    vi.mocked(Math.random)
      .mockReturnValueOnce(0.5)
      .mockReturnValueOnce(0.123456789)
      .mockReturnValueOnce(0.5)
      .mockReturnValueOnce(0.987654321);

    const { result } = renderHook(() => useQRCode());

    act(() => {
      void result.current.generateQR('clock-in');
    });
    await advancePastGeneration();
    const firstToken = result.current.qrData?.token;

    act(() => {
      result.current.markAsUsed();
    });
    expect(result.current.status).toBe('used');

    act(() => {
      void result.current.generateQR('clock-in');
    });
    await advancePastGeneration();

    expect(result.current.status).toBe('active');
    expect(result.current.qrData?.token).toBeTruthy();
    expect(result.current.qrData?.token).not.toBe(firstToken);
  });

  it('keeps the expiration fallback within 30 to 60 seconds', async () => {
    const { result } = renderHook(() => useQRCode());

    act(() => {
      void result.current.generateQR('clock-out');
    });
    await advancePastGeneration();

    const expiresInSeconds = Math.round(
      ((result.current.qrData?.expiresAt ?? 0) - (result.current.qrData?.createdAt ?? 0)) / 1000,
    );

    expect(expiresInSeconds).toBeGreaterThanOrEqual(30);
    expect(expiresInSeconds).toBeLessThanOrEqual(60);
  });
});
