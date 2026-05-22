import { useEffect, useRef, useState } from 'react'
import { Html5Qrcode } from 'html5-qrcode'
import { Camera, QrCode } from 'lucide-react'
import {
  recordAttendanceScan,
  type AttendanceScanType,
} from './Features/attendanceEvents'

const SCANNER_ELEMENT_ID = 'reader'

const SCANNER_CONFIG = {
  primaryCamera: { facingMode: 'environment' as const },
  fallbackCamera: { facingMode: 'user' as const },
  fps: 10,
  qrbox: { width: 220, height: 220 },
}

function getScanTypeFromCode(code: string): AttendanceScanType | null {
  const normalizedCode = code.trim().toUpperCase()

  if (normalizedCode === 'CLOCK_IN') {
    return 'clock-in'
  }

  if (normalizedCode === 'CLOCK_OUT') {
    return 'clock-out'
  }

  return null
}

function ScanQRCard() {
  const [scannerOpen, setScannerOpen] = useState(false)
  const [scanResult, setScanResult] = useState('')
  const [scanError, setScanError] = useState('')

  const scannerRef = useRef<Html5Qrcode | null>(null)

  const stopScanner = async () => {
    if (!scannerRef.current) return

    try {
      await scannerRef.current.stop()
      scannerRef.current.clear()
    } catch {
      //
    } finally {
      scannerRef.current = null
      setScannerOpen(false)
    }
  }

  const handleScanSuccess = async (decodedText: string) => {
    const scanType = getScanTypeFromCode(decodedText)

    if (!scanType) {
      setScanError('Invalid QR code. Use CLOCK_IN or CLOCK_OUT.')
      setScanResult('')
      await stopScanner()
      return
    }

    recordAttendanceScan(scanType)
    setScanError('')
    setScanResult(decodedText.trim().toUpperCase())

    await stopScanner()
  }

  const handleDemoScan = (code: 'CLOCK_IN' | 'CLOCK_OUT') => {
    const scanType = getScanTypeFromCode(code)

    if (!scanType) return

    recordAttendanceScan(scanType)
    setScanError('')
    setScanResult(code)
  }

  const startScanner = async () => {
    try {
      setScanError('')
      setScanResult('')

      const scanner = new Html5Qrcode(SCANNER_ELEMENT_ID)
      scannerRef.current = scanner

      try {
        await scanner.start(
          SCANNER_CONFIG.primaryCamera,
          {
            fps: SCANNER_CONFIG.fps,
            qrbox: SCANNER_CONFIG.qrbox,
          },
          handleScanSuccess,
          () => {},
        )
      } catch {
        await scanner.start(
          SCANNER_CONFIG.fallbackCamera,
          {
            fps: SCANNER_CONFIG.fps,
            qrbox: SCANNER_CONFIG.qrbox,
          },
          handleScanSuccess,
          () => {},
        )
      }

      setScannerOpen(true)
    } catch {
      setScanError(
        'Could not open camera. Please allow camera access and try again.',
      )
    }
  }

  useEffect(() => {
    return () => {
      if (scannerRef.current) {
        scannerRef.current.stop().catch(() => undefined)
      }
    }
  }, [])

  return (
    <div className="rounded-3xl border border-slate-300 bg-white px-6 py-10 shadow-sm">
      {!scannerOpen ? (
        <div className="flex flex-col items-center justify-center text-center">
          {/* QR Icon Box */}
          <div className="flex h-16 w-16 items-center justify-center rounded-3xl bg-[#1E567D]">
            <QrCode size={37} className="text-white" strokeWidth={2.25} />
          </div>

          {/* Heading */}
          <h2 className="mt-8 text-4xl font-bold text-[#093C5D]">
            Ready to scan
          </h2>

          {/* Description */}
          <p className="mt-3 text-xl text-slate-600">
            Camera works offline. Events will sync automatically.
          </p>

          {/* Button */}
          <button
            type="button"
            onClick={startScanner}
            className="mt-8 flex items-center gap-3 rounded-2xl bg-[#093C5D] px-6 py-3 text-lg font-semibold text-white transition hover:bg-[#0B4B73]"
          >
            <Camera size={22} />
            Open camera
          </button>

          {/* Divider */}
          <div className="mt-10 w-full border-t border-slate-200" />

          {/* Demo Buttons */}
          <div className="mt-6 text-center">
            <p className="text-lg text-slate-600">
              No camera? Try demo scan:
            </p>

            <div className="mt-4 flex flex-wrap justify-center gap-3">
              <button
                type="button"
                onClick={() => handleDemoScan('CLOCK_IN')}
                className="rounded-xl border border-slate-300 bg-white px-5 py-3 text-lg font-medium text-[#093C5D] hover:bg-slate-50"
              >
                Demo: Clock In
              </button>

              <button
                type="button"
                onClick={() => handleDemoScan('CLOCK_OUT')}
                className="rounded-xl border border-slate-300 bg-white px-5 py-3 text-lg font-medium text-[#093C5D] hover:bg-slate-50"
              >
                Demo: Clock Out
              </button>
            </div>
          </div>
        </div>
      ) : (
        <div className="text-center">
          <div id={SCANNER_ELEMENT_ID} className="mx-auto max-w-xl" />

          <button
            type="button"
            onClick={stopScanner}
            className="mt-6 rounded-xl border border-slate-300 px-6 py-3 text-slate-700 hover:bg-slate-50"
          >
            Stop camera
          </button>
        </div>
      )}

      {scanError && (
        <div className="mt-6 rounded-xl bg-red-50 px-4 py-3 text-red-700">
          {scanError}
        </div>
      )}

      {scanResult && (
        <div className="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-emerald-700">
          Scanned result: {scanResult}
        </div>
      )}
    </div>
  )
}

export default ScanQRCard