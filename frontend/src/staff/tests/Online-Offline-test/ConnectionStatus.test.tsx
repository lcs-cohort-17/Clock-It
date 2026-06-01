import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { render, screen, act, renderHook } from "@testing-library/react";
import "@testing-library/jest-dom";
import { useOnlineStatus } from "../../components/dashboard/use-online-status";
import { ConnectionStatus } from "../../components/dashboard/ConnectionStatus";

function setOnline(value: boolean) {
  Object.defineProperty(navigator, "onLine", { configurable: true, value });
  window.dispatchEvent(new Event(value ? "online" : "offline"));
}

describe("useOnlineStatus", () => {
  beforeEach(() => setOnline(true));

  it("returns true when the browser is online", () => {
    const { result } = renderHook(() => useOnlineStatus());
    expect(result.current).toBe(true);
  });

  it("updates to false when an offline event fires", () => {
    const { result } = renderHook(() => useOnlineStatus());
    act(() => setOnline(false));
    expect(result.current).toBe(false);
  });

  it("updates back to true when an online event fires", () => {
    const { result } = renderHook(() => useOnlineStatus());
    act(() => setOnline(false));
    act(() => setOnline(true));
    expect(result.current).toBe(true);
  });

  it("removes listeners on unmount", () => {
    const removeSpy = vi.spyOn(window, "removeEventListener");
    const { unmount } = renderHook(() => useOnlineStatus());
    unmount();
    expect(removeSpy).toHaveBeenCalledWith("online", expect.any(Function));
    expect(removeSpy).toHaveBeenCalledWith("offline", expect.any(Function));
    removeSpy.mockRestore();
  });
});

describe("<ConnectionStatus />", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    setOnline(true);
  });
  afterEach(() => vi.useRealTimers());

  it("shows the Online pill by default", () => {
    render(<ConnectionStatus />);
    expect(screen.getByText(/online/i)).toBeInTheDocument();
  });

  it("shows Offline when the browser goes offline", () => {
    render(<ConnectionStatus />);
    act(() => setOnline(false));
    expect(screen.getByText(/offline/i)).toBeInTheDocument();
  });

  it("shows Syncing then Synced after reconnecting", () => {
    render(<ConnectionStatus />);
    act(() => setOnline(false));
    act(() => setOnline(true));
    expect(screen.getByText(/syncing/i)).toBeInTheDocument();
    act(() => vi.advanceTimersByTime(1500));
    expect(screen.getByText(/synced/i)).toBeInTheDocument();
  });
});
