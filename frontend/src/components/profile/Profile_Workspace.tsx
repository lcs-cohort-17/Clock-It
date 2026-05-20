import { useEffect, useState, type ChangeEvent, type FormEvent } from "react";
import { MdClose, MdEdit, MdSave } from "react-icons/md";

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
  const [savedUser, setSavedUser] = useState(user);

  const [draftUser, setDraftUser] = useState(user);

  const [isEditing, setIsEditing] = useState(false);

  const [avatarPreviewUrl, setAvatarPreviewUrl] = useState<
    string | undefined
  >();

  const activeAvatarUrl =
    avatarPreviewUrl ??
    (isEditing
      ? draftUser.avatarUrl
      : savedUser.avatarUrl);

  useEffect(() => {
    return () => {
      if (avatarPreviewUrl) {
        URL.revokeObjectURL(avatarPreviewUrl);
      }
    };
  }, [avatarPreviewUrl]);

  const handleEdit = () => {
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

    const updatedUser: UserProfileData = {
      ...draftUser,
      avatarUrl:
        avatarPreviewUrl ??
        draftUser.avatarUrl ??
        savedUser.avatarUrl,
    };

    setSavedUser(updatedUser);

    setIsEditing(false);

    setAvatarPreviewUrl(undefined);

    onSave?.(updatedUser);
  };

  const handleInputChange = (
    e: ChangeEvent<HTMLInputElement>
  ) => {
    const { name, value } = e.target;

    setDraftUser((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleAvatarChange = (
    e: ChangeEvent<HTMLInputElement>
  ) => {
    const file = e.target.files?.[0];

    if (!file || !file.type.startsWith("image/")) {
      return;
    }

    setAvatarPreviewUrl((prev) => {
      if (prev) {
        URL.revokeObjectURL(prev);
      }

      return URL.createObjectURL(file);
    });
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
                onChange={handleAvatarChange}
              />

              <label
                htmlFor="avatar-upload"
                className="cursor-pointer rounded-xl bg-blue-100 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-200"
              >
                Upload image
              </label>
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
                <span className="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                  Online
                </span>
              </div>
            </div>
          </div>

          <div className="flex gap-3">
            {isEditing ? (
              <>
                <button
                  type="submit"
                  className="inline-flex items-center gap-2 rounded-xl bg-[#093C5D] px-4 py-2 text-white"
                >
                  <MdSave size={18} />
                  Save
                </button>

                <button
                  type="button"
                  onClick={handleCancel}
                  className="inline-flex items-center gap-2 rounded-xl border px-4 py-2"
                >
                  <MdClose size={18} />
                  Cancel
                </button>
              </>
            ) : (
              <button
                type="button"
                onClick={handleEdit}
                className="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-[#093C5D]"
              >
                <MdEdit size={18} />
                Edit profile
              </button>
            )}
          </div>
        </div>

        <div className="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
          {[
            {
              label: "Full Name",
              name: "fullName",
            },
            {
              label: "Email",
              name: "email",
              type: "email",
            },
            {
              label: "Employee ID",
              name: "employeeId",
            },
            {
              label: "Role",
              name: "role",
            },
          ].map((field) => (
            <label
              key={field.name}
              className="grid gap-2"
            >
              <span className="text-xs uppercase text-slate-500">
                {field.label}
              </span>

              {isEditing ? (
                <input
                  name={field.name}
                  type={field.type || "text"}
                  value={
                    draftUser[
                      field.name as keyof UserProfileData
                    ] ?? ""
                  }
                  onChange={handleInputChange}
                  className="rounded-xl border px-4 py-3"
                />
              ) : (
                <p className="rounded-xl border bg-slate-50 px-4 py-3 text-sm">
                  {
                    savedUser[
                      field.name as keyof UserProfileData
                    ]
                  }
                </p>
              )}
            </label>
          ))}
        </div>
      </form>
    </section>
  );
}

export default Profile_Workspace;