import { useEffect, useRef, useState } from "react";

import {
  MdChatBubbleOutline,
  MdDeleteOutline,
  MdClose,
} from "react-icons/md";

export function Support_Workspace() {
  /*
    Loading states
  */
  const [isContactingAdmin, setIsContactingAdmin] = useState(false);
  const [isClearingCache, setIsClearingCache] = useState(false);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const contactResetTimeoutRef = useRef<number | undefined>(
    undefined
  );

  useEffect(() => {
    return () => {
      if (contactResetTimeoutRef.current) {
        window.clearTimeout(contactResetTimeoutRef.current);
      }
    };
  }, []);

  // =========================
  // CONTACT ADMIN
  // =========================
  const handleContactAdmin = async () => {
    setIsContactingAdmin(true);
    
    try {
      // Open email client immediately (no artificial delay)
      window.open("mailto:admin@clock-it.com?subject=Clock-It Support Request");
    } catch (error) {
      console.error("Failed to open mail client:", error);
    } finally {
      // Small delay to ensure loading state is visible
      contactResetTimeoutRef.current =
        window.setTimeout(() => {
        setIsContactingAdmin(false);
      }, 300);
    }
  };

  // =========================
  // CLEAR CACHE
  // =========================
  const handleClearCache = async () => {
    setIsClearingCache(true);
    
    try {
      // Clear localStorage items related to attendance
      const keysToRemove = [
        'offline-attendance',
        'pending-events',
        'clockit-offline-data',
        'attendance-records'
      ];
      
      keysToRemove.forEach(key => localStorage.removeItem(key));
      
      // Clear any IndexedDB databases if they exist
      try {
        const databases =
  typeof indexedDB.databases === "function"
    ? await indexedDB.databases()
    : [];
        databases.forEach(db => {
          if (db.name && db.name.includes('clockit')) {
            indexedDB.deleteDatabase(db.name);
          }
        });
      } catch {
        console.warn("No IndexedDB databases to clear");
        }
      
      // Small delay for UX feedback
      await new Promise(resolve => setTimeout(resolve, 500));
      
      // Optional: Show success feedback
      alert('Local cache cleared successfully');
    } catch (error) {
      console.error("Failed to clear cache:", error);
      alert('Failed to clear cache. Please try again.');
    } finally {
      setIsClearingCache(false);
      setShowConfirmModal(false);
    }
  };

  return (
    <>
      <section
        className="
          w-full
          rounded-3xl
          border
          border-slate-200
          bg-white
          p-6
          shadow-sm
          dark:border-[#163856]
          dark:bg-[#0b2142]
        "
      >
        {/* =========================
            HEADER
           ========================= */}
        <div>
          <h2
            className="
              text-2xl
              font-semibold
              text-[#093C5D]
              dark:text-[#eff6ff]
            "
          >
            Support & Data
          </h2>

          <p className="mt-1 text-slate-500 dark:text-[#cbd5ff]">
            Manage support requests and local
            application data.
          </p>
        </div>

        {/* =========================
            SUPPORT ACTIONS
           ========================= */}
        <div className="mt-6 space-y-4">
          {/* Contact Admin */}
          <button
            type="button"
            disabled={isContactingAdmin}
            onClick={handleContactAdmin}
            aria-live="polite"
            className="
              flex
              w-fit
              items-center
              gap-3
              rounded-2xl
              border
              border-blue-100
              bg-blue-50
              px-5
              py-4
              text-left
              text-sm
              font-semibold
              text-blue-700
              transition-all
              duration-300
              dark:border-blue-400/40
              dark:bg-blue-400/15
              dark:text-blue-100

              hover:bg-blue-100
              dark:hover:bg-blue-400/25

              focus:outline-none
              focus:ring-4
              focus:ring-blue-200
              dark:focus:ring-blue-300/30

              disabled:cursor-not-allowed
              disabled:opacity-60
            "
          >
            <MdChatBubbleOutline size={20} />

            <span>
              {isContactingAdmin
                ? "Opening support..."
                : "Contact admin"}
            </span>
          </button>

          {/* Version Info */}
          <div
            className="
              rounded-2xl
              border
              border-slate-200
              bg-slate-50
              p-4
              dark:border-[#23456f]
              dark:bg-[#081a2f]
            "
          >
            <p className="text-sm text-slate-600 dark:text-[#cbd5ff]">
              App version{" "}
              <span className="font-semibold">
                1.0.0
              </span>
            </p>

            <p className="mt-2 text-sm text-slate-600 dark:text-[#cbd5ff]">
              Pending offline events:{" "}
              <span className="font-semibold text-[#093C5D] dark:text-[#eff6ff]">
                0
              </span>
            </p>
          </div>

          {/* =========================
              DANGER ZONE
             ========================= */}
          <div className="border-t border-slate-200 pt-5 dark:border-[#23456f]">
            <p
              className="
                mb-3
                text-xs
                font-bold
                uppercase
                tracking-wider
                text-slate-400
                dark:text-[#9bb3d1]
              "
            >
              Danger Zone
            </p>

            <button
              type="button"
              onClick={() => setShowConfirmModal(true)}
              disabled={isClearingCache}
              className="
                flex
                w-fit
                items-center
                gap-3
                rounded-2xl
                border
                border-red-100
                bg-red-50
                px-5
                py-4
                text-left
                text-sm
                font-semibold
                text-red-600
                transition-all
                duration-300
                dark:border-red-400/40
                dark:bg-red-400/15
                dark:text-red-100

                hover:bg-red-100
                dark:hover:bg-red-400/25

                focus:outline-none
                focus:ring-4
                focus:ring-red-200
                dark:focus:ring-red-300/30

                disabled:cursor-not-allowed
                disabled:opacity-60
              "
            >
              <MdDeleteOutline size={20} />
              <span>
                {isClearingCache ? "Clearing cache..." : "Clear local cache"}
              </span>
            </button>

            <p className="mt-3 text-xs text-slate-400 dark:text-[#9bb3d1]">
              Clears offline attendance records
              stored on this device.
            </p>
          </div>
        </div>
      </section>

      {/* =========================
          CONFIRMATION MODAL
         ========================= */}
      {showConfirmModal && (
        <div
          className="
            fixed inset-0 z-50
            flex items-center justify-center
            bg-black/50
            p-4
          "
          onClick={() => setShowConfirmModal(false)}
        >
          <div
            className="
              w-full max-w-md
              rounded-2xl
              bg-white
              p-6
              shadow-xl
              dark:border
              dark:border-[#163856]
              dark:bg-[#0b2142]
            "
            onClick={(e) => e.stopPropagation()}
          >
            {/* Modal Header */}
            <div className="flex items-center justify-between">
              <h3 className="text-xl font-bold text-[#093C5D] dark:text-[#eff6ff]">
                Clear local cache?
              </h3>
              <button
                type="button"
                onClick={() => setShowConfirmModal(false)}
                className="
                  rounded-lg
                  p-1
                  text-slate-400
                  hover:bg-slate-100
                  hover:text-slate-600
                  dark:text-[#cbd5ff]
                  dark:hover:bg-[#103553]
                  dark:hover:text-white
                  transition
                "
              >
                <MdClose size={20} />
              </button>
            </div>

            {/* Modal Body */}
            <p className="mt-4 text-slate-600 dark:text-[#cbd5ff]">
              This will remove all offline attendance records from this device.
              This action cannot be undone.
            </p>

            <p className="mt-2 text-sm text-slate-500 dark:text-[#9bb3d1]">
              You will need an internet connection to sync data afterwards.
            </p>

            {/* Modal Actions */}
            <div className="mt-6 flex gap-3">
              <button
                type="button"
                onClick={handleClearCache}
                disabled={isClearingCache}
                className="
                  flex-1
                  rounded-xl
                  bg-red-600
                  px-4
                  py-2.5
                  text-sm
                  font-semibold
                  text-white
                  transition
                  hover:bg-red-700
                  focus:outline-none
                  focus:ring-4
                  focus:ring-red-200
                  disabled:cursor-not-allowed
                  disabled:opacity-60
                "
              >
                {isClearingCache ? "Clearing..." : "Yes, clear cache"}
              </button>
              
              <button
                type="button"
                onClick={() => setShowConfirmModal(false)}
                disabled={isClearingCache}
                className="
                  flex-1
                  rounded-xl
                  border
                  border-slate-200
                  bg-white
                  px-4
                  py-2.5
                  text-sm
                  font-semibold
                  text-slate-600
                  transition
                  hover:bg-slate-50
                  dark:border-[#23456f]
                  dark:bg-[#081a2f]
                  dark:text-[#eff6ff]
                  dark:hover:bg-[#103553]
                  focus:outline-none
                  focus:ring-4
                  focus:ring-slate-200
                  disabled:cursor-not-allowed
                  disabled:opacity-60
                "
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

export default Support_Workspace;
