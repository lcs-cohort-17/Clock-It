export default function DemoAccounts() {
  return (
    <div className="rounded-xl border border-[#3B7597]/20 bg-[#F5F5F5] p-4">
      <h3 className="text-sm font-semibold text-[#093C5D]">Demo accounts</h3>
      <div className="mt-2 space-y-2 text-sm">
        <div>
          <span className="font-medium text-[#093C5D]">Admin (email):</span>{' '}
          {/* EDIT: change demo admin credentials if needed */}
          <span className="text-[#3B7597]">taaraa@clockit.com / admin123</span>
        </div>
        <div>
          <span className="font-medium text-[#093C5D]">Staff (email):</span>{' '}
          <span className="text-[#3B7597]">shaheed@clockit.com / staff123</span>
        </div>
        <div>
          <span className="font-medium text-[#093C5D]">Employee ID:</span>{' '}
          <span className="text-[#3B7597]">EMP004 (admin) or EMP001 (staff)</span>
        </div>
        <div className="text-xs text-[#3B7597]/80">
          <span className="font-medium">Microsoft/Google:</span> uses the same email addresses above
        </div>
      </div>
    </div>
  );
}
