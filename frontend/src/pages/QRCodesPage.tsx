import { useState } from "react";
import { Plus } from "lucide-react";
import AdminLayout from "./components/qr/AdminLayout.tsx";
import QRCodeCard from "./components/qr/QRCodeCard.tsx";
import QRModal from "..QRModal.tsx";

export default function QRCodesPage() {
  const [open, setOpen] = useState(false);
  const [type, setType] = useState<"clock-in" | "clock-out">("clock-in");

  return (
    <AdminLayout active="qr">
      <div className="max-w-6xl mx-auto">
        <div className="flex flex-wrap items-start justify-between gap-4 mb-8">
          <div>
            <h1 className="text-2xl font-semibold">QR Code Generator</h1>
            <p className="text-sm text-muted-foreground mt-1">
              Create unique QR codes for clock-in / clock-out points.
            </p>
          </div>
          <button onClick={() => { setType("clock-in"); setOpen(true); }}
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium">
            <Plus className="h-4 w-4" /> New QR code
          </button>
        </div>

        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <QRCodeCard title="Global Clock In" location="HQ Entrance"
            createdAt="20 May 09:16" type="Clock in" initialToken="global-clock-in-001" />
          <QRCodeCard title="Global Clock Out" location="HQ Exit"
            createdAt="20 May 09:16" type="Clock out" initialToken="global-clock-out-001" />
        </div>
      </div>

      <QRModal open={open} onClose={() => setOpen(false)} type={type} />
    </AdminLayout>
  );
}
