import { AuthLogo } from './AuthLogo'

const PROMO_STATS = [
  { value: '<2s', label: 'Clock-in time' },
  { value: '100%', label: 'Offline ready' },
  { value: 'Live', label: 'Sheets sync' },
]

export function PromoPanel() {
  return (
    <aside className="promo-dot-grid relative hidden min-h-screen flex-col justify-between px-14 py-[57px] text-white min-[1200px]:flex">
      <AuthLogo layout="desktop" />

      <div className="max-w-[620px]">
        {/* Edit this approved marketing copy only when the Figma or product copy changes. */}
        <h1 className="max-w-[620px] text-[46px] font-bold leading-[1.22] tracking-normal">
          Real-time attendance.
          <br />
          Offline-first.
          <br />
          Built for frontline staff.
        </h1>
        <p className="mt-8 max-w-[620px] text-[21px] font-medium leading-[1.42] text-white/86">
          Fast QR-based clock-in. Live onsite visibility. Seamless Google Sheets sync &mdash;
          wherever your team works.
        </p>

        <div className="mt-14 grid max-w-[560px] grid-cols-3 gap-5">
          {PROMO_STATS.map((stat) => (
            <div className="rounded-[12px] bg-white/12 p-5" key={stat.label}>
              <p className="text-[31px] font-bold leading-tight">{stat.value}</p>
              <p className="mt-2 text-[16px] font-semibold text-white/68">{stat.label}</p>
            </div>
          ))}
        </div>
      </div>

      <p className="text-[16px] text-white/64">&copy; Clock It &middot; Secure attendance for modern teams</p>
    </aside>
  )
}
