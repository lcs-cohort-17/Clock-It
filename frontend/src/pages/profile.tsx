import Profile_Workspace from "../components/profile/Profile_Workspace";
import Support_Workspace from "../components/profile/Support_Workspace";
import PasswordCard from "../components/profile/PasswordCard";
import type { UserProfileData } from "../components/profile/UserProfileData";

export function UserProfilePage({
  user,
}: {
  user: UserProfileData;
}) {
  const handleProfileSave = (
    updatedUser: UserProfileData
  ) => {
    console.log("Profile saved:", updatedUser);
  };

  return (
    <section className="min-h-screen w-full bg-[#EEF3F8] dark:bg-[#081a2f] p-4 md:p-6 xl:p-8">
      <div className="mb-8">
        <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-[#9bb3d1]">
          Profile
        </p>

        <h1 className="mt-2 text-3xl font-bold text-slate-800 dark:text-[#eff6ff] md:text-4xl">
          User Profile
        </h1>

        <p className="mt-2 text-sm text-slate-500 dark:text-[#9bb3d1]">
          Your account information
        </p>
      </div>

      <div className="mx-auto flex max-w-5xl flex-col gap-6">
        <Profile_Workspace
          user={user}
          onSave={handleProfileSave}
        />

        {/* <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <h2 className="text-2xl font-semibold text-[#093C5D]">
            Password
          </h2>

          <p className="mt-1 text-slate-500">
            Password management section placeholder.
          </p>

          <div className="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
            <p className="text-sm text-slate-500">
              Password section will be implemented here by another developer.
            </p>
          </div>
        </section> */}
          <div className="max-w-3xl mx-auto p-7 w-full">
            <PasswordCard />
          </div>







        <Support_Workspace />
      </div>
    </section>
  );
}

export default UserProfilePage;
