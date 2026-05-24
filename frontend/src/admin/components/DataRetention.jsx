/* Natheefah | Data Retention */
import { useState } from 'react';
import { Database } from 'lucide-react';

const DataRetention = () => {
  const [retentionDays, setRetentionDays] = useState('365');
  const [isLoading, setIsLoading] = useState(false);
  const [message, setMessage] = useState({ text: '', type: '' });

  const isValid = (value) => {
    const num = Number(value);
    return Number.isInteger(num) && num > 0;
  };

  const handlePurge = async () => {
    setMessage({ text: '', type: '' });

    if (!isValid(retentionDays)) {
      setMessage({ text: 'Please enter a positive whole number of days.', type: 'error' });
      return;
    }

    const confirmDelete = window.confirm(
      `Are you sure you want to permanently delete all attendance records older than ${retentionDays} days? This action cannot be undone.`
    );
    if (!confirmDelete) return;

    setIsLoading(true);
    try {
      await new Promise((resolve) => setTimeout(resolve, 1500));
      setMessage({
        text: `Successfully purged all attendance records older than ${retentionDays} days.`,
        type: 'success',
      });
    } catch (error) {
      setMessage({
        text: `Error: ${error.message}. Please try again.`,
        type: 'error',
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="w-full rounded-xl border border-[#cbd7df] bg-white px-[30px] py-[30px] text-[#002f4f] shadow-sm">
      <div className="flex items-start gap-[21px]">
        <div className="flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-xl bg-[#fff4df] text-[#f59e0b]">
          <Database className="h-[22px] w-[22px]" />
        </div>

        <div className="w-full min-w-0">
          <div className="mb-[27px]">
            <h2 className="text-[22px] font-bold leading-tight text-[#002f4f]">Data retention</h2>
            <p className="mt-[7px] text-base leading-normal text-[#245575]">
              Auto-purge attendance records older than the threshold.
            </p>
          </div>

          <div className="flex flex-col gap-[14px] sm:flex-row sm:items-end">
            <div className="w-full max-w-[486px]">
              <label
                htmlFor="retention-days"
                className="mb-[11px] block text-sm font-semibold text-[#002f4f]"
              >
                Keep records for (days)
              </label>
              <input
                id="retention-days"
                type="number"
                value={retentionDays}
                onChange={(event) => setRetentionDays(event.target.value)}
                min="1"
                step="1"
                className="h-[50px] w-full rounded-xl border border-[#cbd7df] bg-[#f8fafb] px-4 text-base font-medium text-[#002f4f] outline-none transition focus:border-[#06466b] focus:ring-2 focus:ring-[#06466b]/15"
              />
            </div>

            <button
              type="button"
              onClick={handlePurge}
              disabled={isLoading}
              className="h-[50px] rounded-xl border border-[#cbd7df] bg-white px-5 text-base font-semibold text-[#002f4f] transition hover:border-[#06466b] hover:bg-[#f8fafb] disabled:cursor-not-allowed disabled:opacity-70"
            >
              {isLoading ? 'Purging...' : 'Purge old records now'}
            </button>
          </div>

          {message.text && (
            <div
              className={`mt-4 rounded-lg border-l-4 p-3 text-sm ${
                message.type === 'success'
                  ? 'border-[#9ccf57] bg-[#f1f8e8] text-[#5d9a1b]'
                  : 'border-red-500 bg-red-50 text-red-700'
              }`}
              role="alert"
            >
              {message.text}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default DataRetention;
