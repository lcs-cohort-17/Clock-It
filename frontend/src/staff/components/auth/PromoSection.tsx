import { Clock, Wifi, Users } from 'lucide-react';

export default function PromoSection() {
  return (
    // EDIT: Change gradient colors if design differs; currently matches indigo-to-purple from screenshots
    <div className="flex h-full w-full flex-col justify-center bg-gradient-to-br from-[#093C5D] via-[#3B7597] to-[#9CB07A] p-8 text-white lg:p-12">
      {/* Logo and App Name – larger spacing, centred */}
      <div className="mb-16 flex items-center gap-4">
        {/* Logo container with rounded-xl and white overlay */}
        <div className="flex h-14 w-14 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
          <Clock className="h-8 w-8" />
        </div>
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Clock It</h1>
          <p className="text-sm text-white/75">Attendance Tracker</p>
        </div>
      </div>

      {/* Feature Highlights – matching the exact spacing and text sizes from the prototype */}
      <div className="space-y-8">
        <h2 className="text-xl font-semibold">Why you’ll love it</h2>
        <div className="space-y-6">
          <div className="flex items-start gap-4">
            <Clock className="mt-1 h-6 w-6 shrink-0" />
            <div>
              <h3 className="font-medium text-lg">Real‑time attendance</h3>
              <p className="text-sm text-white/70">Know exactly who’s in and out right now.</p>
            </div>
          </div>
          <div className="flex items-start gap-4">
            <Wifi className="mt-1 h-6 w-6 shrink-0" />
            <div>
              <h3 className="font-medium text-lg">Offline‑first</h3>
              <p className="text-sm text-white/70">Works even when the internet doesn’t.</p>
            </div>
          </div>
          <div className="flex items-start gap-4">
            <Users className="mt-1 h-6 w-6 shrink-0" />
            <div>
              <h3 className="font-medium text-lg">Frontline staff support</h3>
              <p className="text-sm text-white/70">Built for the people on the ground.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}