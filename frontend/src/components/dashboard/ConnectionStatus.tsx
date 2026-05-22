import { useEffect, useRef, useState } from "react";
import { Loader2, Check, AlertCircle } from "lucide-react";
import { useOnlineStatus } from "./use-online-status";

type SyncState = "idle" | "syncing" | "synced" | "failed";

export function ConnectionStatus() {
  const isOnline = useOnlineStatus();
  const wasOffline = useRef(false);
  const [sync, setSync] = useState<SyncState>("idle");

  useEffect(() => {
    if (!isOnline) {
      wasOffline.current = true;
      setSync("idle");
      return;
    }
    if (wasOffline.current) {
      wasOffline.current = false;
      setSync("syncing");
      const t = setTimeout(() => setSync("synced"), 1500);
      return () => clearTimeout(t);
    }
  }, [isOnline]);

  return (
    <div className="flex items-center gap-3">
      <span
        className={`inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium ${
          isOnline
            ? "bg-emerald-100 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-100"
            : "bg-red-100 text-red-700 dark:bg-red-400/15 dark:text-red-100"
        }`}
      >
        <span className="relative flex h-2 w-2">
          {isOnline && (
            <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
          )}
          <span
            className={`relative inline-flex h-2 w-2 rounded-full ${
              isOnline ? "bg-emerald-500" : "bg-red-500"
            }`}
          />
        </span>
        {isOnline ? "Online" : "Offline"}
      </span>

      {sync === "syncing" && (
        <span className="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-[#cbd5ff]">
          <Loader2 className="h-4 w-4 animate-spin" /> Syncing…
        </span>
      )}
      {sync === "synced" && (
        <span className="inline-flex items-center gap-1 text-sm text-emerald-600 dark:text-emerald-100">
          <Check className="h-4 w-4" /> Synced
        </span>
      )}
      {sync === "failed" && (
        <span className="inline-flex items-center gap-1 text-sm text-red-600 dark:text-red-100">
          <AlertCircle className="h-4 w-4" /> Sync failed
        </span>
      )}
    </div>
  );
}
