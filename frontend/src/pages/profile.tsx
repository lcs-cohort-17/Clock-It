import Profile_Workspace from "../components/profile/Profile_Workspace";
import Support_Workspace from "../components/profile/Support_Workspace";
import PasswordCard from "../components/profile/PasswordCard";
import type { UserProfileData } from "../components/profile/UserProfileData";

export function UserProfilePage({
  user,
}: {
  user: UserProfileData;
}) {
  return (
    <section className="min-h-screen w-full bg-[#EEF3F8] p-4 md:p-6 xl:p-8">
      <div className="mb-8">
        <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">
          Profile
        </p>

        <h1 className="mt-2 text-3xl font-bold text-slate-800 md:text-4xl">
          User Profile
        </h1>

        <p className="mt-2 text-sm text-slate-500">
          Your account information
        </p>
      </div>

      {/* All boxes now have the same width */}
      <div className="mx-auto flex max-w-2xl flex-col gap-6">
        <Profile_Workspace user={user} />
        <PasswordCard />
        <Support_Workspace />
      </div>
    </section>
  );
}

export default UserProfilePage;