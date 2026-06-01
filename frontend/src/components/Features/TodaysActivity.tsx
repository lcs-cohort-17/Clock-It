import React from 'react';

interface Activity {
  firstClockIn?: string;
  lastClockOut?: string;
  totalHours?: number;
}

interface TodaysActivityProps {
  activity: Activity | null;
}

const TodaysActivity: React.FC<TodaysActivityProps> = ({ activity }) => {
  if (!activity || !activity.firstClockIn) {
    return (
      <div className="activity-block flex min-h-28 items-center justify-center rounded-xl bg-[#F7F7F7] p-6 text-center text-base font-medium text-[#093C5D]">
        No clock events today yet.
      </div>
    );
  }
  return (
    <div className="activity-block grid gap-4 rounded-xl bg-white p-6 shadow-sm md:grid-cols-3">
      <div>
        <p className="text-xs font-bold uppercase text-slate-400">First clock-in</p>
        <p className="mt-1 text-xl font-bold text-[#093C5D]">{activity.firstClockIn}</p>
      </div>
      <div>
        <p className="text-xs font-bold uppercase text-slate-400">Last clock-out</p>
        <p className="mt-1 text-xl font-bold text-[#093C5D]">{activity.lastClockOut}</p>
      </div>
      <div>
        <p className="text-xs font-bold uppercase text-slate-400">Total hours</p>
        <p className="mt-1 text-xl font-bold text-[#093C5D]">Total hours: {activity.totalHours}</p>
      </div>
    </div>
  );
};

export default TodaysActivity;
