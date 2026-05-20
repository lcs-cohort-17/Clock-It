export default function DemoAccounts() {
  return (
    <div className="rounded-xl border border-gray-200 bg-gray-50 p-4">
      <h3 className="text-sm font-semibold text-gray-600">Demo accounts</h3>
      <div className="mt-2 space-y-2 text-sm">
        <div>
          <span className="font-medium text-gray-800">Admin (email):</span>{' '}
          {/* EDIT: change demo admin credentials if needed */}
          <span className="text-gray-500">taaraa@clockit.com / admin123</span>
        </div>
        <div>
          <span className="font-medium text-gray-800">Staff (email):</span>{' '}
          <span className="text-gray-500">shaheed@clockit.com / staff123</span>
        </div>
        <div>
          <span className="font-medium text-gray-800">Employee ID:</span>{' '}
          <span className="text-gray-500">EMP004 (admin) or EMP001 (staff)</span>
        </div>
        <div className="text-xs text-gray-400">
          <span className="font-medium">Microsoft/Google:</span> uses the same email addresses above
        </div>
      </div>
    </div>
  );
}