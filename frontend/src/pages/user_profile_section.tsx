import React from "react";

// Define the clear structural contract for the user data coming into your component
export interface UserProfileData {
  fullName: string;
  email: string;
  employeeId: string;
  role: string;
  avatarUrl?: string;
}

// Helper function to turn full names into clean, two-letter avatar initials
const getInitials = (name: string): string => {
  if (!name) return "";
  // Split the string into chunks, grab the first letter of each, and slice the first two
  return name.split(" ").map(n => n[0]).join("").toUpperCase().slice(0, 2);
};

// Main functional component responsible for rendering Section 1
export const ProfileCard: React.FC<{ user: UserProfileData }> = ({ user }) => {
  return (
    // Outer card wrap matching the deep navy tone and curved style from your preview
    <div className="w-full bg-[#04243A] rounded-3xl border border-slate-800 p-6 shadow-xl">
      
      {/* Top block grouping the visual avatar and the user's primary identity display */}
      <div className="flex flex-col items-center sm:items-start space-y-4 pb-6 border-b border-slate-800">
        
        {/* Wrapper for the avatar circle, enforcing a fixed, responsive size constraint */}
        <div className="w-20 h-20 rounded-full flex items-center justify-center bg-slate-700 text-xl font-bold text-white tracking-wider overflow-hidden">
          {user.avatarUrl ? (
            // Render the image directly if the source URL is provided in the data object
            <img src={user.avatarUrl} alt={user.fullName} className="w-full h-full object-cover" />
          ) : (
            // Fall back to our clean initials logic if no image exists
            <span>{getInitials(user.fullName)}</span>
          )}
        </div>

        {/* Identity block displaying name and core system anchor string */}
        <div className="text-center sm:text-left">
          {/* Using text-2xl and font-bold to give the name the strongest visual weight */}
          <h2 className="text-2xl font-bold text-white tracking-tight">{user.fullName}</h2>
          {/* Dimmed small text directly under the name for secondary context */}
          <p className="text-sm text-slate-400 mt-0.5">{user.email}</p>
        </div>
      </div>

      {/* Grid container holding the structured information fields beneath the header */}
      <div className="mt-6 space-y-5">
        
        {/* Structured Row Block: Email Detail */}
        <div className="flex flex-col space-y-1">
          {/* Dimmed, medium font weight gives structure to the metadata label */}
          <span className="text-xs text-slate-400 font-medium uppercase tracking-wider">Email</span>
          {/* High contrast text makes reading the actual value low-friction */}
          <span className="text-sm text-slate-100 font-normal break-all">{user.email}</span>
        </div>

        {/* Structured Row Block: Employee Identification Number */}
        <div className="flex flex-col space-y-1">
          {/* Reusing the uppercase tracking class for field identifier uniformity */}
          <span className="text-xs text-slate-400 font-medium uppercase tracking-wider">Employee ID</span>
          {/* Regular font weight keeps the system ID clear and highly scannable */}
          <span className="text-sm text-slate-100 font-normal">{user.employeeId}</span>
        </div>

        {/* Structured Row Block: Assigned Role or System Permission Tier */}
        <div className="flex flex-col space-y-1">
          {/* Maintaining consistent spacing rules down the entire stack */}
          <span className="text-xs text-slate-400 font-medium uppercase tracking-wider">Role</span>
          {/* Capitalized style enforces clean text presentation regardless of database input */}
          <span className="text-sm text-slate-100 font-normal capitalize">{user.role}</span>
        </div>

      </div>
    </div>
  );
};

export const UserProfilePage: React.FC<{ user: UserProfileData }> = ({ user }) => {
  return (
    <section className="flex-1 bg-[#F5F5F5] p-8">
      <div className="mb-6">
        <p className="text-xs font-bold uppercase tracking-wide text-slate-400">
          Profile
        </p>
        <h1 className="mt-2 text-4xl font-bold text-[#093C5D]">
          User Profile
        </h1>
      </div>

      <div className="max-w-xl">
        <ProfileCard user={user} />
      </div>
    </section>
  );
};

export default ProfileCard; 
