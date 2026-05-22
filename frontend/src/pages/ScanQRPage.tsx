import ScanQRCard from '../components/ScanQRCard'
//fix
const SCAN_PAGE_TEXT = {
  title: 'Scan QR Code',
  description:
    'Point your camera at the workplace QR code to clock in or out.',
}

function ScanQRPage() {
  return (
    <div className="min-h-screen bg-[#F5F7FA] px-10 py-6 text-slate-900">
      <div className="mx-auto max-w-6xl">
        {/* Page Heading */}
        <h1 className="text-4xl font-bold text-[#093C5D]">
          {SCAN_PAGE_TEXT.title}
        </h1>

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