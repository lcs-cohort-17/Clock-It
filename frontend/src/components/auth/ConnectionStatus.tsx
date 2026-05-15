type ConnectionStatusProps = {
  isOnline: boolean
}

export function ConnectionStatus({ isOnline }: ConnectionStatusProps) {
  return (
    <div
      aria-live="polite"
      className={`mt-8 flex h-10 items-center gap-3 rounded-[12px] px-4 text-[16px] ${
        isOnline ? 'bg-[#eef3e8] text-[#82bd24]' : 'bg-[#fff0f0] text-[#b42318]'
      }`}
      role="status"
    >
      <svg aria-hidden="true" className="size-4" viewBox="0 0 24 24" fill="none">
        <path
          d={
            isOnline
              ? 'M5 12.4a10 10 0 0 1 14 0M8.5 15.9a5 5 0 0 1 7 0M12 19h.01'
              : 'M5 12.4a10 10 0 0 1 14 0M8.5 15.9a5 5 0 0 1 7 0M4 4l16 16M12 19h.01'
          }
          stroke="currentColor"
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth="2"
        />
      </svg>
      {isOnline ? 'Connected' : 'Offline'}
    </div>
  )
}
