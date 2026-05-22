import ScanQRCard from '../components/ScanQRCard'

const SCAN_PAGE_TEXT = {
  title: 'Scan QR Code',
  description:
    'Point your camera at the workplace QR code to clock in or out.',
}

function ScanQRPage() {
  return (
    <div className="flex-1 bg-[#EEF3F8] dark:bg-[#081a2f] p-8 text-slate-900 dark:text-[#eff6ff]">
      <div className="mx-auto max-w-4xl">
        {/* This page introduces the scanner before the user opens the camera. */}
        <h1
          className="text-4xl font-bold"
          style={{ color: SCAN_PAGE_STYLES.headingColor }}
        >
          {SCAN_PAGE_TEXT.title}
        </h1>
        <p className="mt-3 text-lg text-slate-600 dark:text-[#9bb3d1]">{SCAN_PAGE_TEXT.description}</p>

        {/* Description */}
        <p className="mt-3 text-xl text-slate-600">
          {SCAN_PAGE_TEXT.description}
        </p>

        {/* QR Card */}
        <div className="mt-10">
          <ScanQRCard />
        </div>
      </div>
    </div>
  )
}

export default ScanQRPage