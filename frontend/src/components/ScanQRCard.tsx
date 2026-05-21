export const SCAN_QR_TEXT = {
  badge: 'QR',
  title: 'Workplace QR Code',
  description: 'Staff scan this code with their phone to clock in or out.',
  locationLabel: 'Clock It attendance point',
  codeLabel: 'Site code',
  codeValue: 'CLOCK-IT-SITE-001',
  helperText: 'Keep this screen visible at the workplace entrance.',
}

const SCAN_QR_STYLES = {
  badgeBackground: '#1D547A',
  headingColor: '#093C5D',
}

function ScanQRCard() {
  return (
    <div className="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm md:p-10">
      <div className="grid gap-8 md:grid-cols-[260px_1fr] md:items-center">
        <div className="mx-auto w-full max-w-64">
          <div className="rounded-3xl border border-slate-200 bg-slate-50 p-5">
            <div className="grid aspect-square grid-cols-5 gap-2 rounded-2xl bg-white p-4">
              {Array.from({ length: 25 }).map((_, index) => (
                <span
                  key={index}
                  className={`rounded-sm ${
                    [0, 1, 2, 5, 7, 10, 11, 12, 18, 20, 21, 22, 24].includes(index)
                      ? 'bg-[#093C5D]'
                      : 'bg-slate-100'
                  }`}
                />
              ))}
            </div>
          </div>
        </div>

        <div>
          <div
            className="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl text-lg font-bold text-white"
            style={{ backgroundColor: SCAN_QR_STYLES.badgeBackground }}
          >
            {SCAN_QR_TEXT.badge}
          </div>

          <p className="text-sm font-bold uppercase tracking-wide text-slate-400">
            {SCAN_QR_TEXT.locationLabel}
          </p>

          <h2
            className="mt-2 text-3xl font-semibold"
            style={{ color: SCAN_QR_STYLES.headingColor }}
          >
            {SCAN_QR_TEXT.title}
          </h2>

          <p className="mt-3 text-lg text-slate-600">{SCAN_QR_TEXT.description}</p>

          <div className="mt-6 rounded-2xl bg-slate-50 p-4">
            <p className="text-xs font-bold uppercase tracking-wide text-slate-400">
              {SCAN_QR_TEXT.codeLabel}
            </p>
            <p className="mt-1 font-mono text-lg font-bold text-[#093C5D]">
              {SCAN_QR_TEXT.codeValue}
            </p>
          </div>

          <p className="mt-4 text-sm text-slate-500">{SCAN_QR_TEXT.helperText}</p>
        </div>
      </div>
    </div>
  )
}

export default ScanQRCard
