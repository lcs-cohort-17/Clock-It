import { useState, type ReactNode } from "react";
import {
  LayoutDashboard, QrCode, ClipboardList, Users, Settings, LogOut, Menu,
} from "lucide-react";

const nav = [
  { label: "Dashboard", icon: LayoutDashboard, key: "dashboard" },
  { label: "QR Codes", icon: QrCode, key: "qr" },
  { label: "Attendance Logs", icon: ClipboardList, key: "logs" },
  { label: "User Management", icon: Users, key: "users" },
  { label: "Settings", icon: Settings, key: "settings" },
];

export default function AdminLayout({
  children, active = "qr",
}: { children: ReactNode; active?: string }) {
  const [open, setOpen] = useState(false);

  return (
    <div className="min-h-screen flex bg-muted/30 text-foreground">
      <aside className={`${open ? "translate-x-0" : "-translate-x-full"} md:translate-x-0 fixed md:static z-40 inset-y-0 left-0 w-64 bg-card border-r flex flex-col transition-transform`}>
        <div className="px-6 py-5 border-b">
          <div className="flex items-center gap-2">
            <div className="h-8 w-8 rounded-lg bg-primary text-primary-foreground grid place-items-center font-bold">C</div>
            <div>
              <p className="font-semibold leading-tight">Clock It</p>
              <p className="text-xs text-muted-foreground">admin</p>
            </div>
          </div>
        </div>

        <nav className="flex-1 px-3 py-4 space-y-1">
          {nav.map((item) => {
            const Icon = item.icon;
            const isActive = item.key === active;
            return (
              <button key={item.key}
                className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors ${
                  isActive ? "bg-primary text-primary-foreground" : "hover:bg-muted text-foreground/80"
                }`}>
                <Icon className="h-4 w-4" />
                {item.label}
              </button>
            );
          })}
        </nav>

        <div className="border-t p-4">
          <div className="flex items-center gap-3 mb-3">
            <div className="h-9 w-9 rounded-full bg-muted grid place-items-center text-sm font-medium">PS</div>
            <div className="min-w-0">
              <p className="text-sm font-medium truncate">Priya Singh</p>
              <p className="text-xs text-muted-foreground truncate">admin@clockit.app</p>
            </div>
          </div>
          <button className="w-full flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-muted text-foreground/80">
            <LogOut className="h-4 w-4" /> Log out
          </button>
        </div>
      </aside>

      {open && <div className="fixed inset-0 bg-black/30 z-30 md:hidden" onClick={() => setOpen(false)} />}

      <div className="flex-1 flex flex-col min-w-0">
        <header className="h-14 bg-card border-b flex items-center justify-between px-4 md:px-6">
          <div className="flex items-center gap-2">
            <button className="md:hidden p-2 rounded-md hover:bg-muted" onClick={() => setOpen(true)} aria-label="Open menu">
              <Menu className="h-5 w-5" />
            </button>
            <span className="inline-flex items-center gap-2 text-sm text-muted-foreground">
              <span className="h-2 w-2 rounded-full bg-emerald-500" /> Online
            </span>
          </div>
          <span className="text-sm font-medium">admin</span>
        </header>
        <main className="flex-1 p-4 md:p-8">{children}</main>
      </div>
    </div>
  );
}
