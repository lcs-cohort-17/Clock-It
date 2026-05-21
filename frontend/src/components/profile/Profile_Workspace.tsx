import { useEffect, useState, type ChangeEvent, type FormEvent } from "react";
import { MdClose, MdEdit, MdSave, MdWifi, MdWifiOff } from "react-icons/md";

import type { UserProfileData } from "./UserProfileData";

const getInitials = (name: string): string => {
  if (!name) return "";
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
};

interface ProfileWorkspaceProps {
  user: UserProfileData;
  onSave?: (user: UserProfileData) => void;
}

export function Profile_Workspace({
  user,
  onSave,
}: ProfileWorkspaceProps) {
  // Load saved data from localStorage on initial load
  const loadSavedData = (): UserProfileData => {
    const saved = localStorage.getItem(`user_${user.employeeId}`);
    if (saved) {
      try {
        return JSON.parse(saved);
      } catch {
        return user;
      }
    }
    return user;
  };

  const initialData = loadSavedData();
  
  const [savedUser, setSavedUser] = useState(initialData);
  const [draftUser, setDraftUser] = useState(initialData);
  const [isEditing, setIsEditing] = useState(false);
  const [avatarPreviewUrl, setAvatarPreviewUrl] = useState<string | undefined>();

  // Online/Offline state
  const [isOnline, setIsOnline] = useState<boolean>(navigator.onLine);

  // Listen for connection changes
  useEffect(() => {
    const handleOnline = () => setIsOnline(true);
    const handleOffline = () => setIsOnline(false);

    window.addEventListener("online", handleOnline);
    window.addEventListener("offline", handleOffline);

    return () => {
      window.removeEventListener("online", handleOnline);
      window.removeEventListener("offline", handleOffline);
    };
  }, []);

  const activeAvatarUrl =
    avatarPreviewUrl ??
    (isEditing
      ? draftUser.avatarUrl
      : savedUser.avatarUrl);

  // Save to localStorage whenever savedUser changes
  useEffect(() => {
    localStorage.setItem(`user_${user.employeeId}`, JSON.stringify(savedUser));
    if (savedUser.avatarUrl) {
      localStorage.setItem(`avatar_${user.employeeId}`, savedUser.avatarUrl);
    }
  }, [savedUser, user.employeeId]);

  // Sync when external user prop changes
  useEffect(() => {
    const newSavedData = loadSavedData();
    setSavedUser(newSavedData);
    setDraftUser(newSavedData);
  }, [user]);

  useEffect(() => {
    return () => {
      if (avatarPreviewUrl) {
        URL.revokeObjectURL(avatarPreviewUrl);
      }
    };
  }, [avatarPreviewUrl]);

  const handleEdit = (e?: React.MouseEvent<HTMLButtonElement>) => {
    e?.preventDefault();
    e?.stopPropagation();
    setIsEditing(true);
    setDraftUser({ ...savedUser });
    setAvatarPreviewUrl(undefined);
  };

  const handleCancel = () => {
    setIsEditing(false);
    setDraftUser({ ...savedUser });

    if (avatarPreviewUrl) {
      URL.revokeObjectURL(avatarPreviewUrl);
    }
    setAvatarPreviewUrl(undefined);
  };

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();

    const finalAvatarUrl = avatarPreviewUrl ?? draftUser.avatarUrl ?? savedUser.avatarUrl;
    
    const updatedUser: UserProfileData = {
      ...draftUser,
      avatarUrl: finalAvatarUrl,
    };

    setSavedUser(updatedUser);
    setIsEditing(false);
    setAvatarPreviewUrl(undefined);
    onSave?.(updatedUser);
  };

  const handleInputChange = (e: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setDraftUser((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleAvatarChange = (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file || !file.type.startsWith("image/")) return;

    // Convert to Base64 for permanent storage
    const reader = new FileReader();
    reader.onloadend = () => {
      const base64String = reader.result as string;
      setAvatarPreviewUrl(base64String);
    };
    reader.readAsDataURL(file);
  };

  return (
    <section className="w-full rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <form onSubmit={handleSubmit}>
        <div className="flex flex-col gap-5 border-b border-slate-200 pb-6 lg:flex-row lg:items-start lg:justify-between">
          <div className="flex flex-col items-center gap-4 sm:flex-row">
            <div className="flex flex-col items-center gap-3">
              <div className="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-[#093C5D] text-white shadow-md">
                {activeAvatarUrl ? (
                  <img
                    src={activeAvatarUrl}
                    alt="Profile"
                    className="h-full w-full object-cover"
                  />
                ) : (
                  <span>
                    {getInitials(
                      isEditing
                        ? draftUser.fullName
                        : savedUser.fullName
                    )}
                  </span>
                )}
              </div>

              <input
                id="avatar-upload"
                type="file"
                accept="image/*"
                className="hidden"
                disabled={!isEditing}
                onChange={handleAvatarChange}
              />

              {isEditing && (
                <label
                  htmlFor="avatar-upload"
                  className="cursor-pointer rounded-xl bg-blue-100 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-200"
                >
                  Upload image
                </label>
              )}
            </div>

            <div className="text-center sm:text-left">
              <h2 className="text-2xl font-bold text-[#093C5D]">
                {isEditing
                  ? draftUser.fullName
                  : savedUser.fullName}
              </h2>

              <p className="mt-1 text-sm text-slate-500">
                {isEditing
                  ? draftUser.email
                  : savedUser.email}
              </p>

              <div className="mt-3 flex flex-wrap gap-2">
                {isOnline ? (
                  <span className="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                    <MdWifi size={12} />
                    Online
                  </span>
                ) : (
                  <span className="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                    <MdWifiOff size={12} />
                    Offline
                  </span>
                )}
              </div>

              {isEditing && !isOnline && (
                <p className="mt-2 text-xs text-amber-600">
                  You are offline. Changes will be saved locally.
                </p>
              )}
            </div>
          </div>

          <div className="flex gap-3">
            {isEditing ? (
              <>
                <button
                  type="submit"
                  className="inline-flex items-center gap-2 rounded-xl bg-[#093C5D] px-4 py-2 text-white hover:bg-[#0a4a70] transition"
                >
                  <MdSave size={18} />
                  Save
                </button>

                <button
                  type="button"
                  onClick={handleCancel}
                  className="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50 transition"
                >
                  <MdClose size={18} />
                  Cancel
                </button>
              </>
            ) : (
              <button
                type="button"
                onClick={handleEdit}
                className="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-[#093C5D] hover:bg-gray-50 transition"
              >
                <MdEdit size={18} />
                Edit profile
              </button>
            )}
          </div>
        </div>

        <div className="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
          {[
            { label: "Full Name", name: "fullName" },
            { label: "Email", name: "email", type: "email" },
            { label: "Employee ID", name: "employeeId" },
            { label: "Role", name: "role" },
          ].map((field) => (
            <label key={field.name} className="grid gap-2">
              <span className="text-xs font-medium uppercase tracking-wider text-slate-500">
                {field.label}
              </span>

              {isEditing ? (
                <input
                  name={field.name}
                  type={field.type || "text"}
                  value={draftUser[field.name as keyof UserProfileData] ?? ""}
                  onChange={handleInputChange}
                  className="rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-800 focus:border-[#093C5D] focus:outline-none focus:ring-2 focus:ring-[#093C5D]/20"
                />
              ) : (
                <p className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                  {savedUser[field.name as keyof UserProfileData]}
                </p>
              )}
            </label>
          ))}
        </div>

        {!isOnline && (
          <div className="mt-6 rounded-xl bg-amber-50 p-4 border border-amber-200">
            <p className="text-sm text-amber-700 flex items-center gap-2">
              <MdWifiOff size={16} />
              You are currently offline. Changes will sync when connection is restored.
            </p>
          </div>
        )}
      </form>
    </section>
  );
}

export default Profile_Workspace;