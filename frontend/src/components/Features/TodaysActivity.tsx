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
      <div className="activity-block flex min-h-28 items-center justify-center rounded-xl bg-[#F7F7F7] dark:bg-[#0b2142] p-6 text-center text-base font-medium text-[#093C5D] dark:text-[#eff6ff]">
        No clock events today yet.
      </div>
    );
  }
  return (
    <div className="activity-block grid gap-4 rounded-xl bg-white dark:bg-[#0b2142] p-6 shadow-sm md:grid-cols-3 dark:border dark:border-[#163856]">
      <div>
        <p className="text-xs font-bold uppercase text-slate-400 dark:text-[#b1bf86]">First clock-in</p>
        <p className="mt-1 text-xl font-bold text-[#093C5D] dark:text-[#d9f0b1]">{activity.firstClockIn}</p>
      </div>
      <div>
        <p className="text-xs font-bold uppercase text-slate-400 dark:text-[#b1bf86]">Last clock-out</p>
        <p className="mt-1 text-xl font-bold text-[#093C5D] dark:text-[#d9f0b1]">{activity.lastClockOut}</p>
      </div>
      <div>
        <p className="text-xs font-bold uppercase text-slate-400 dark:text-[#b1bf86]">Total hours</p>
        <p className="mt-1 text-xl font-bold text-[#093C5D] dark:text-[#d9f0b1]">Total hours: {activity.totalHours}</p>
      </div>
    </div>
  );
};

export default TodaysActivity;
