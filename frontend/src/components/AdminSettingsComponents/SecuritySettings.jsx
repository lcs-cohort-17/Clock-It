import { useState } from "react";

// --- Icons ---
const ShieldIcon = () => (
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
  </svg>
);

const ClockIcon = () => (
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <circle cx="12" cy="12" r="10" />
    <polyline points="12 6 12 12 16 14" />
  </svg>
);

const LockIcon = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
  </svg>
);

const CheckIcon = () => (
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
    <polyline points="20 6 9 17 4 12" />
  </svg>
);

// --- Toggle Switch ---
const Toggle = ({ checked, onChange, disabled }) => (
  <button
    role="switch"
    aria-checked={checked}
    onClick={() => !disabled && onChange(!checked)}
    style={{
      width: 48,
      height: 28,
      borderRadius: 999,
      border: "none",
      cursor: disabled ? "not-allowed" : "pointer",
      background: checked ? "#0f3460" : "#d1d5db",
      position: "relative",
      transition: "background 0.25s ease",
      flexShrink: 0,
      outline: "none",
      boxShadow: checked ? "0 0 0 3px rgba(15,52,96,0.18)" : "none",
    }}
  >
    <span
      style={{
        position: "absolute",
        top: 3,
        left: checked ? 23 : 3,
        width: 22,
        height: 22,
        borderRadius: "50%",
        background: "#fff",
        transition: "left 0.22s cubic-bezier(.4,0,.2,1)",
        boxShadow: "0 1px 4px rgba(0,0,0,0.18)",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
      }}
    />
  </button>
);

// --- Security Row (for toggle rows) ---
const SecurityRow = ({ icon, label, description, toggle, disabled }) => (
  <div
    style={{
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      background: "#f8fafc",
      borderRadius: 12,
      padding: "16px 20px",
      gap: 16,
    }}
  >
    <div style={{ display: "flex", alignItems: "flex-start", gap: 14 }}>
      <span
        style={{
          color: "#0f3460",
          marginTop: 2,
          flexShrink: 0,
          opacity: 0.75,
        }}
      >
        {icon}
      </span>
      <div>
        <div style={{ fontWeight: 600, fontSize: 14, color: "#1e293b", lineHeight: 1.4 }}>
          {label}
        </div>
        <div style={{ fontSize: 13, color: "#64748b", marginTop: 2, lineHeight: 1.5 }}>
          {description}
        </div>
      </div>
    </div>
    {toggle}
  </div>
);

// --- RBAC Static Row ---
const RBACRow = () => (
  <div style={{ padding: "4px 4px 0" }}>
    <div style={{ fontWeight: 600, fontSize: 14, color: "#1e293b", marginBottom: 4 }}>
      Role-based access (RBAC)
    </div>
    <div style={{ fontSize: 13, color: "#64748b", lineHeight: 1.6 }}>
      Always enforced. Staff cannot access admin areas. All role checks are server-side.
    </div>
  </div>
);

// --- Main Component ---
export default function SecuritySettings() {
  const [timeout, setTimeout] = useState(30);
  const [inputValue, setInputValue] = useState("30");
  const [encryptionEnabled, setEncryptionEnabled] = useState(true);
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState("");

  const handleSave = () => {
    const val = parseInt(inputValue, 10);
    if (isNaN(val) || val < 1 || val > 1440) {
      setError("Please enter a value between 1 and 1440 minutes.");
      return;
    }
    setError("");
    setTimeout(val);
    setSaved(true);
    setTimeout(() => setSaved(false), 2200);
  };

  const handleInput = (e) => {
    setInputValue(e.target.value);
    setError("");
    setSaved(false);
  };

  return (
    <div
      style={{
        fontFamily: "'DM Sans', 'Segoe UI', sans-serif",
        background: "#fff",
        borderRadius: 18,
        boxShadow: "0 1px 4px rgba(15,52,96,0.07), 0 4px 24px rgba(15,52,96,0.06)",
        padding: "28px 32px 32px",
        maxWidth: 780,
        margin: "0 auto",
        border: "1px solid #e8edf4",
      }}
    >
      {/* Section Header */}
      <div style={{ display: "flex", alignItems: "center", gap: 14, marginBottom: 24 }}>
        <div
          style={{
            width: 44,
            height: 44,
            borderRadius: 12,
            background: "#eef3fa",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            color: "#0f3460",
            flexShrink: 0,
          }}
        >
          <ShieldIcon />
        </div>
        <div>
          <div style={{ fontWeight: 700, fontSize: 18, color: "#0f3460", letterSpacing: "-0.3px" }}>
            Security
          </div>
          <div style={{ fontSize: 13, color: "#64748b", marginTop: 1 }}>
            Session management, encryption, and access control.
          </div>
        </div>
      </div>

      {/* Divider */}
      <div style={{ height: 1, background: "#f1f5f9", marginBottom: 24 }} />

      {/* Session Timeout */}
      <div style={{ marginBottom: 22 }}>
        <label
          style={{
            display: "flex",
            alignItems: "center",
            gap: 7,
            fontSize: 13,
            fontWeight: 600,
            color: "#334155",
            marginBottom: 10,
          }}
        >
          <ClockIcon />
          Session timeout (minutes)
        </label>
        <div style={{ display: "flex", gap: 10, alignItems: "center" }}>
          <input
            type="number"
            min={1}
            max={1440}
            value={inputValue}
            onChange={handleInput}
            onKeyDown={(e) => e.key === "Enter" && handleSave()}
            style={{
              width: "100%",
              maxWidth: 360,
              padding: "11px 16px",
              fontSize: 15,
              fontWeight: 500,
              color: "#1e293b",
              background: "#f8fafc",
              border: error ? "1.5px solid #ef4444" : "1.5px solid #e2e8f0",
              borderRadius: 10,
              outline: "none",
              transition: "border-color 0.18s",
              fontFamily: "inherit",
              boxSizing: "border-box",
            }}
            onFocus={(e) => {
              if (!error) e.target.style.borderColor = "#0f3460";
            }}
            onBlur={(e) => {
              if (!error) e.target.style.borderColor = "#e2e8f0";
            }}
          />
          <button
            onClick={handleSave}
            style={{
              padding: "11px 22px",
              borderRadius: 10,
              border: "none",
              background: saved ? "#166534" : "#0f3460",
              color: "#fff",
              fontWeight: 600,
              fontSize: 13.5,
              cursor: "pointer",
              display: "flex",
              alignItems: "center",
              gap: 6,
              transition: "background 0.2s, transform 0.12s",
              fontFamily: "inherit",
              flexShrink: 0,
              letterSpacing: "0.01em",
              boxShadow: "0 2px 8px rgba(15,52,96,0.13)",
            }}
            onMouseEnter={(e) => { if (!saved) e.currentTarget.style.background = "#162d52"; }}
            onMouseLeave={(e) => { if (!saved) e.currentTarget.style.background = "#0f3460"; }}
          >
            {saved ? (
              <>
                <CheckIcon /> Saved
              </>
            ) : (
              "Save"
            )}
          </button>
        </div>
        {error && (
          <div style={{ color: "#ef4444", fontSize: 12.5, marginTop: 7, fontWeight: 500 }}>
            {error}
          </div>
        )}
        {!error && (
          <div style={{ color: "#94a3b8", fontSize: 12.5, marginTop: 7 }}>
            Users will be automatically logged out after {timeout} minute{timeout !== 1 ? "s" : ""} of inactivity.
          </div>
        )}
      </div>

      {/* Data Encryption Toggle */}
      <SecurityRow
        icon={<LockIcon />}
        label="Data encryption (in transit & at rest)"
        description="Recommended for compliance"
        toggle={
          <Toggle
            checked={encryptionEnabled}
            onChange={setEncryptionEnabled}
          />
        }
      />

      {/* Divider */}
      <div style={{ height: 1, background: "#f1f5f9", margin: "20px 0" }} />

      {/* RBAC */}
      <RBACRow />
    </div>
  );
}
