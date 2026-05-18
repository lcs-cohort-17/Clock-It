type AuthLogoProps = {
  layout?: 'desktop' | 'stacked'
}

export function AuthLogo({ layout = 'stacked' }: AuthLogoProps) {
  const isDesktop = layout === 'desktop'

  return (
    <div
      className={
        isDesktop
          ? 'flex items-center gap-3 text-white'
          : 'flex flex-col items-center gap-4 text-[#002b49]'
      }
    >
      <div
        aria-hidden="true"
        className={
          isDesktop
            ? 'flex size-[50px] items-center justify-center rounded-[14px] bg-white/12'
            : 'flex size-[70px] items-center justify-center rounded-[18px] bg-[#285f82] shadow-[0_18px_36px_rgb(40_95_130/0.22)]'
        }
      >
        <svg className={isDesktop ? 'size-7' : 'size-9'} viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="8.5" stroke="currentColor" strokeWidth="2" />
          <path
            d="M12 7.5v5l3.4 2"
            stroke="currentColor"
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth="2"
          />
        </svg>
      </div>
      {/* Change the app display name here if the approved product name changes. */}
      <span className={isDesktop ? 'text-[28px] font-bold' : 'text-[38px] font-bold leading-none'}>
        Clock It
      </span>
    </div>
  )
}
