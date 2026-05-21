// This file defines TypeScript interfaces for the dashboard data structures used in the backend of the Clock-It application.
export type AttendanceType = "clock-in" | "clock-out";

export interface Profile {
  
  first_name: string;
  last_name: string;
}

export interface RecentActivityRecord {
  profile_id: string;
  event_time: string;
  event_type: AttendanceType;
  sync_status: string;
  device_info: string;
  profiles: Profile;
}

export interface CurrentlyOnsiteRecord {
  profile_id: string;
  event_time: string;
  event_type: AttendanceType;
  location: string;
  profiles: Profile;
}