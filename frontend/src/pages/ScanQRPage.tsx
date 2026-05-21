import ScanQRCard from '../components/ScanQRCard'

const SCAN_PAGE_TEXT = {
  title: 'Scan QR Code',
  description: 'Display this workplace QR code so staff can scan it from their phone.',
}

const SCAN_PAGE_STYLES = {
  headingColor: '#093C5D',
}

function ScanQRPage() {
  return (
    <div className="flex-1 bg-slate-50 p-8 text-slate-900">
      <div className="mx-auto max-w-4xl">
        {/* This page displays the workplace QR layout that staff scan with their phone. */}
        <h1
          className="text-4xl font-bold"
          style={{ color: SCAN_PAGE_STYLES.headingColor }}
        >
          {SCAN_PAGE_TEXT.title}
        </h1>
        <p className="mt-3 text-lg text-slate-600">{SCAN_PAGE_TEXT.description}</p>

        <div className="mt-8">
          <ScanQRCard />
        </div>
      </div>
    </div>
  )
}

export default ScanQRPage
