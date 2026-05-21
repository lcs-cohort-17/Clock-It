import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { MdQrCodeScanner } from 'react-icons/md'
import { recordAttendanceScan, type AttendanceScanType } from './attendanceEvents'

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

export default function ScanQRFlow() {
  const navigate = useNavigate()
  const [scanCode, setScanCode] = useState('')
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault()
    const scanType = getScanTypeFromCode(scanCode)

    if (!scanType) {
      setError('Invalid QR code. Use CLOCK_IN or CLOCK_OUT.')
      setMessage('')
      return
    }

    const savedEvent = recordAttendanceScan(scanType)
    setError('')
    setMessage(`${scanType === 'clock-in' ? 'Clocked in' : 'Clocked out'} at ${savedEvent.time}`)
  }

  return (
    <div className="flex-1 bg-slate-50 p-8 text-slate-900">
      <div className="mx-auto max-w-2xl rounded-2xl bg-white p-6 shadow-sm">
        <div className="mb-6 flex h-12 w-12 items-center justify-center rounded-xl bg-[#093C5D] text-white">
          <MdQrCodeScanner size={24} />
        </div>

        <h1 className="text-4xl font-bold text-[#093C5D]">Scan QR Code</h1>
        <p className="mt-3 text-lg text-slate-600">
          Enter the QR scan code from the phone scan to update your attendance status.
        </p>

        <form onSubmit={handleSubmit} className="mt-8 space-y-4">
          <label className="block text-sm font-semibold text-slate-600">
            QR scan code
            <input
              value={scanCode}
              onChange={event => setScanCode(event.target.value)}
              placeholder="CLOCK_IN or CLOCK_OUT"
              className="mt-1 w-full rounded-lg border border-slate-200 p-3"
            />
          </label>

          {error && <p className="text-sm font-semibold text-red-600">{error}</p>}
          {message && <p className="text-sm font-semibold text-emerald-600">{message}</p>}

          <div className="flex flex-wrap gap-3">
            <button
              type="submit"
              className="rounded-xl bg-[#093C5D] px-5 py-3 text-sm font-bold text-white"
            >
              Submit scan
            </button>
            <button
              type="button"
              onClick={() => navigate('/staff-dashboard')}
              className="rounded-xl bg-slate-100 px-5 py-3 text-sm font-bold text-[#093C5D]"
            >
              Back to dashboard
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}
