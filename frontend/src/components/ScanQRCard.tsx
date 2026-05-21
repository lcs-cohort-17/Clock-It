import { useEffect, useRef, useState } from 'react'
import { Html5Qrcode } from 'html5-qrcode'

const SCANNER_ELEMENT_ID = 'reader'

const SCANNER_CONFIG = {
  primaryCamera: { facingMode: 'environment' as const },
  fallbackCamera: { facingMode: 'user' as const },
  fps: 10,
  qrbox: { width: 220, height: 220 },
}

const SCAN_QR_TEXT = {
  badge: 'QR',
  title: 'Ready to scan',
  description: 'Use a device camera to scan the workplace QR code.',
  openButton: 'Open camera',
  stopButton: 'Stop camera',
  resultLabel: 'Scanned result:',
  cameraError: 'Could not open camera. Please allow camera access and try again.',
}

const SCAN_QR_STYLES = {
  badgeBackground: '#1D547A',
  headingColor: '#093C5D',
  buttonBackground: '#093C5D',
  buttonHoverBackground: '#0b4b73',
}

function ScanQRCard() {
  const [scannerOpen, setScannerOpen] = useState(false)
  const [scanResult, setScanResult] = useState('')
  const [scanError, setScanError] = useState('')
  const [openButtonHovered, setOpenButtonHovered] = useState(false)
  const scannerRef = useRef<Html5Qrcode | null>(null)

  const stopScanner = async () => {
    if (!scannerRef.current) {
      return
    }

    try {
      await scannerRef.current.stop()
      scannerRef.current.clear()
    } catch {
      // Ignore stop errors if the scanner is already closed.
    } finally {
      scannerRef.current = null
      setScannerOpen(false)
    }
  }

  const handleScanSuccess = async (decodedText: string) => {
    // Save the scanned QR value, then close the camera.
    setScanResult(decodedText)
    await stopScanner()
  }

  const startScanner = async () => {
    try {
      // Clear old messages before opening the camera again.
      setScanError('')
      setScanResult('')

      const scanner = new Html5Qrcode(SCANNER_ELEMENT_ID)
      scannerRef.current = scanner

      try {
        // Try the back camera first because it is usually best for scanning QR codes.
        await scanner.start(
          SCANNER_CONFIG.primaryCamera,
          {
            fps: SCANNER_CONFIG.fps,
            qrbox: SCANNER_CONFIG.qrbox,
          },
          handleScanSuccess,
          () => {
            // Ignore scan noise while the camera searches.
          },
        )
      } catch {
        await scanner.start(
          SCANNER_CONFIG.fallbackCamera,
          {
            fps: SCANNER_CONFIG.fps,
            qrbox: SCANNER_CONFIG.qrbox,
          },
          handleScanSuccess,
          () => {
            // Ignore scan noise while the camera searches.
          },
        )
      }

      setScannerOpen(true)
    } catch {
      setScanError(SCAN_QR_TEXT.cameraError)
      scannerRef.current = null
    }
  }

  useEffect(() => {
    // Stop the scanner if the user leaves the page while the camera is still active.
    return () => {
      if (scannerRef.current) {
        scannerRef.current.stop().catch(() => undefined)
      }
    }
  }, [])

  return (
    <div className="rounded-3xl border border-slate-200 bg-white p-10 shadow-sm">
      {!scannerOpen ? (
        <div className="text-center">
          {/* This is the idle view shown before the camera starts. */}
          <div
            className="mx-auto flex h-28 w-28 items-center justify-center rounded-3xl text-4xl text-white"
            style={{ backgroundColor: SCAN_QR_STYLES.badgeBackground }}
          >
            {SCAN_QR_TEXT.badge}
          </div>
          <h2
            className="mt-8 text-3xl font-semibold"
            style={{ color: SCAN_QR_STYLES.headingColor }}
          >
            {SCAN_QR_TEXT.title}
          </h2>
          <p className="mt-3 text-lg text-slate-600">{SCAN_QR_TEXT.description}</p>
          <button
            type="button"
            onClick={startScanner}
            onMouseEnter={() => setOpenButtonHovered(true)}
            onMouseLeave={() => setOpenButtonHovered(false)}
            className="mt-6 rounded-xl px-6 py-3 text-white transition"
            style={{
              backgroundColor: openButtonHovered
                ? SCAN_QR_STYLES.buttonHoverBackground
                : SCAN_QR_STYLES.buttonBackground,
            }}
          >
            {SCAN_QR_TEXT.openButton}
          </button>
        </div>
      ) : (
        <div className="text-center">
          {/* The QR library will place the live camera view inside this box. */}
          <div id={SCANNER_ELEMENT_ID} className="mx-auto max-w-xl" />
          <button
            type="button"
            onClick={stopScanner}
            className="mt-6 rounded-xl border border-slate-300 px-6 py-3 text-slate-700 transition hover:bg-slate-50"
          >
            {SCAN_QR_TEXT.stopButton}
          </button>
        </div>
      )}

      {scanResult && (
        <div className="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-emerald-700">
          {SCAN_QR_TEXT.resultLabel} {scanResult}
        </div>
      )}

      {scanError && (
        <div className="mt-6 rounded-xl bg-rose-50 px-4 py-3 text-rose-700">
          {scanError}
        </div>
      )}
    </div>
  )
}

export default ScanQRCard
