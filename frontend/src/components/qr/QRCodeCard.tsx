import { useState } from "react";
import QRCode from "react-qr-code";
import { Download, RefreshCw, Ban } from "lucide-react";

type Props = {
  title: string;
  location: string;
  createdAt: string;
  type: "Clock in" | "Clock out";
  initialToken: string;
};

export default function QRCodeCard({ title, location, createdAt, type, initialToken }: Props) {
  const [token, setToken] = useState(initialToken);
  const [revoked, setRevoked] = useState(false);

  const regenerate = () => {
    setToken(`${type.toLowerCase().replace(" ", "-")}-${crypto.randomUUID()}`);
    setRevoked(false);
  };

  const downloadPNG = () => {
    const svg = document.getElementById(`qr-${initialToken}`);
    if (!svg) return;
    const data = new XMLSerializer().serializeToString(svg);
    const blob = new Blob([data], { type: "image/svg+xml" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url; a.download = `${title}.svg`; a.click();
    URL.revokeObjectURL(url);
  };

  return (
    <div className="bg-card border rounded-xl p-5 flex flex-col">
      <div className="flex items-start justify-between mb-1">
        <div>
          <h3 className="font-semibold">{title}</h3>
          <p className="text-xs text-muted-foreground mt-0.5">{location} · Created {createdAt}</p>
        </div>
        <span className="text-xs px-2 py-1 rounded-md bg-secondary text-secondary-foreground">{type}</span>
      </div>

      <div className="my-4 grid place-items-center bg-white rounded-lg p-4 border">
        {revoked ? (
          <div className="h-[180px] w-[180px] grid place-items-center text-sm text-destructive font-medium">Revoked</div>
        ) : (
          <QRCode id={`qr-${initialToken}`} value={token} size={180} />
        )}
      </div>

      {revoked && <p className="text-xs text-destructive font-medium mb-3 text-center">Revoked</p>}

      <div className="grid grid-cols-3 gap-2 mt-auto">
        <button onClick={downloadPNG} disabled={revoked}
          className="inline-flex items-center justify-center gap-1.5 text-xs font-medium px-2 py-2 rounded-md border hover:bg-muted disabled:opacity-50">
          <Download className="h-3.5 w-3.5" /> PNG
        </button>
        <button onClick={regenerate}
          className="inline-flex items-center justify-center gap-1.5 text-xs font-medium px-2 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90">
          <RefreshCw className="h-3.5 w-3.5" /> New
        </button>
        <button onClick={() => setRevoked(true)} disabled={revoked}
          className="inline-flex items-center justify-center gap-1.5 text-xs font-medium px-2 py-2 rounded-md border border-destructive/30 text-destructive hover:bg-destructive/10 disabled:opacity-50">
          <Ban className="h-3.5 w-3.5" /> Revoke
        </button>
      </div>
    </div>
  );
}
