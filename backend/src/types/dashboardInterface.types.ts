//types/adminDashboard.types.ts file

export type AttendanceType = "in" | "out";
export interface Profile {
  first_name: string;
  last_name: string;
}

export interface RecentActivityRecord {
  
  profile_id: string;
  event_time: string;
  event_type: AttendanceType;
  sync_status: boolean;
  device_info: string;
profiles: Profile;
}

export interface CurrentlyOnsiteRecord {
  
  profile_id: string;
  event_time: string;
  location: string;
profiles: Profile;


}