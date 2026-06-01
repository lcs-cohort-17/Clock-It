type ForgotPasswordProps = {
  onClose: () => void
  onLogin: (credentials: { method: 'email'; email: string; password: string }) => void
}

export default function ForgotPassword({ onClose, onLogin }: ForgotPasswordProps) {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div className="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
        <h2 className="text-xl font-bold text-[#093C5D]">Reset password</h2>
        <p className="mt-2 text-sm text-[#3B7597]">
          Demo mode: use one of the sample accounts to sign in.
        </p>
        <div className="mt-6 flex justify-end gap-3">
          <button
            type="button"
            onClick={onClose}
            className="rounded-lg px-4 py-2 text-sm font-semibold text-[#3B7597]"
          >
            Close
          </button>
          <button
            type="button"
            onClick={() => onLogin({ method: 'email', email: 'shaheed@clockit.com', password: 'staff123' })}
            className="rounded-lg bg-[#093C5D] px-4 py-2 text-sm font-semibold text-white hover:bg-[#3B7597]"
          >
            Use demo staff
          </button>
        </div>
      </div>
    </div>
  )
}
