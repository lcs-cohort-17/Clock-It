/* Natheefah | Data Retention */
import { useState } from 'react';

const DataRetention = () => {
  const [retentionDays, setRetentionDays] = useState(30);
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
      `⚠️ Are you sure you want to permanently delete all attendance records older than ${retentionDays} days? This action cannot be undone.`
    );
    if (!confirmDelete) return;

    setIsLoading(true);
    try {
      await new Promise((resolve) => setTimeout(resolve, 1500));
      const shouldSucceed = Math.random() > 0.1;
      if (shouldSucceed) {
        setMessage({
          text: `✅ Successfully purged all attendance records older than ${retentionDays} days.`,
          type: 'success',
        });
      } else {
        throw new Error('Simulated server error');
      }
    } catch (error) {
      setMessage({
        text: `❌ Error: ${error.message}. Please try again.`,
        type: 'error',
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="bg-light-grey rounded-xl p-7 my-6 shadow-sm border border-[rgba(43,67,83,0.1)]">
      <h2 className="text-[1.35rem] font-semibold text-deep-navy mb-6 border-l-4 border-mid-blue pl-4">
        Data Retention
      </h2>

      <div className="mb-6">
        <label htmlFor="retention-days" className="block text-sm font-medium text-deep-navy mb-2">
          Keep records for (days):
        </label>
        <input
          id="retention-days"
          type="number"
          value={retentionDays}
          onChange={(e) => setRetentionDays(e.target.value)}
          min="1"
          step="1"
          className="w-48 px-3 py-2 text-base border border-[#d1d9e6] rounded-lg bg-white transition-all focus:outline-none focus:border-mid-blue focus:ring-2 focus:ring-mid-blue/20"
        />
      </div>

      <button
        onClick={handlePurge}
        disabled={isLoading}
        className="bg-deep-navy text-white border-none rounded-lg px-5 py-2.5 font-medium text-sm cursor-pointer transition-all inline-flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed hover:bg-mid-blue hover:-translate-y-px hover:shadow-md active:translate-y-px"
      >
        {isLoading ? (
          <>
            <svg className="w-4 h-4 animate-spin" viewBox="0 0 24 24">
              <circle className="fill-none stroke-white/20 stroke-2" cx="12" cy="12" r="10" />
              <path
                className="fill-none stroke-white stroke-2 stroke-linecap-round [stroke-dasharray:30] [stroke-dashoffset:10]"
                d="M12 2a10 10 0 0 1 10 10"
              />
            </svg>
            Purge in progress...
          </>
        ) : (
          'Purge Old Records'
        )}
      </button>

      {message.text && (
        <div
          className={`mt-3 p-3 rounded-md text-sm ${
            message.type === 'success'
              ? 'bg-olive-green/15 text-olive-green border-l-3 border-olive-green'
              : 'bg-red-100/10 text-[#b02a37] border-l-3 border-[#b02a37]'
          }`}
          role="alert"
        >
          {message.text}
        </div>
      )}
    </div>
  );
};

export default DataRetention;
