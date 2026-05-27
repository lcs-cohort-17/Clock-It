import { useState } from 'react';
import { Clock, Lock, ShieldCheck } from 'lucide-react';

const Toggle = ({ checked, onChange }) => (
  <button
    type="button"
    role="switch"
    aria-label="Toggle data encryption"
    aria-checked={checked}
    onClick={() => onChange(!checked)}
    className={`relative h-[30px] w-[56px] shrink-0 rounded-full border-0 transition-colors focus:outline-none focus:ring-2 focus:ring-[#06466b]/25 focus:ring-offset-2 ${
      checked ? 'bg-[#06466b]' : 'bg-[#cbd7df]'
    }`}
  >
    <span
      className={`absolute left-[3px] top-[3px] h-6 w-6 rounded-full bg-white shadow-sm transition-transform duration-200 ${
        checked ? 'translate-x-[26px]' : 'translate-x-0'
      }`}
    />
  </button>
);

export default function SecuritySettings() {
  const [timeout, setTimeout] = useState('30');
  const [encryptionEnabled, setEncryptionEnabled] = useState(true);

  return (
    <div className="w-full rounded-xl border border-[#cbd7df] bg-white px-[30px] py-[30px] text-[#002f4f] shadow-sm">
      <div className="flex items-start gap-[21px]">
        <div className="flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-xl bg-[#eef6fb] text-[#06466b]">
          <ShieldCheck className="h-[22px] w-[22px]" />
        </div>

        <div className="w-full min-w-0">
          <div className="mb-[23px]">
            <h2 className="text-[22px] font-bold leading-tight text-[#002f4f]">Security</h2>
            <p className="mt-[7px] text-base leading-normal text-[#245575]">
              Session management, encryption, and access control.
            </p>
          </div>

          <div className="mb-[21px]">
            <label
              htmlFor="session-timeout"
              className="mb-[9px] flex items-center gap-2 text-sm font-semibold text-[#002f4f]"
            >
              <Clock className="h-4 w-4" />
              Session timeout (minutes)
            </label>
            <input
              id="session-timeout"
              type="number"
              min="1"
              max="1440"
              value={timeout}
              onChange={(event) => setTimeout(event.target.value)}
              className="h-[50px] w-full max-w-[486px] rounded-xl border border-[#cbd7df] bg-[#f8fafb] px-4 text-base font-medium text-[#002f4f] outline-none transition focus:border-[#06466b] focus:ring-2 focus:ring-[#06466b]/15"
            />
          </div>

          <div className="mb-[21px] flex min-h-[74px] items-center justify-between gap-4 rounded-xl bg-[#f8fafb] px-4 py-4">
            <div className="flex items-start gap-[14px]">
              <Lock className="mt-1 h-5 w-5 shrink-0 text-[#06466b]" />
              <div>
                <h3 className="text-base font-bold text-[#002f4f]">
                  Data encryption (in transit &amp; at rest)
                </h3>
                <p className="mt-1 text-sm text-[#245575]">Recommended for compliance</p>
              </div>
            </div>
            <Toggle checked={encryptionEnabled} onChange={setEncryptionEnabled} />
          </div>

          <div className="min-h-[78px] rounded-xl bg-[#fdfdfd] px-4 py-4">
            <h3 className="text-base font-bold text-[#002f4f]">Role-based access (RBAC)</h3>
            <p className="mt-1 text-sm text-[#245575]">
              Always enforced. Staff cannot access admin areas. All role checks are server-side.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
