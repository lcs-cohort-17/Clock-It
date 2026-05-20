// import { Request, Response } from "express";
// import { fetchRecentActivity,fetchCurrentlyOnsite } from "../models/adminDashboardDb.js";

// import { formatRecentActivity,formatOnsite } from "../services/adminDashboard.service.js";

// //GET /recent-activity
 
// export async function getRecentActivity(req: Request, res: Response) {
//   try {
//     const page = Number(req.query.page ?? 1);
//     const limit = Number(req.query.limit ?? 10);

//     const rawData = await fetchRecentActivity(page, limit);
//     const normalizedData = rawData.map((item: any) => ({
//       ...item,
//       profiles: Array.isArray(item.profiles) ? item.profiles[0] : item.profiles,
//     }));
//     const formatted = formatRecentActivity(normalizedData);

//     return res.json(formatted);
//   } catch (error) {
//     return res.status(500).json({ message: "Failed to fetch recent activity" });
//   }
// }


//  // GET /onsite

// export async function getCurrentlyOnsite(req: Request, res: Response) {
//  const rawData = await fetchCurrentlyOnsite();

// const normalizedData = rawData.map((item: any) => ({
//   ...item,
//   profiles: Array.isArray(item.profiles)
//     ? item.profiles[0]
//     : item.profiles,
// }));

// const formatted = formatOnsite(normalizedData);

// return res.json(formatted);
// }
//adminDashboardCon.ts

import { Request, Response } from "express";
import { fetchRecentActivity, fetchCurrentlyOnsite } from "../models/adminDashboardDb.js";
import { formatRecentActivity, formatOnsite } from "../services/adminDashboard.service.js";

// GET /recent-activity
export async function getRecentActivityController(req: Request, res: Response) {
  try {
    const page = Number(req.query.page) || 1;
    const limit = Number(req.query.limit) || 10;

    const rawData = await fetchRecentActivity(page, limit);
    const normalizedData = rawData.map((item: any) => ({
      ...item,
      profiles: Array.isArray(item.profiles) ? item.profiles[0] : item.profiles,
    }));
    const formatted = formatRecentActivity(normalizedData);

    return res.status(200).json({
      status: "success",
      data: formatted,
    });
  } catch (error) {
    return res.status(500).json({
      status: "error",
      message: "Failed to fetch recent activity",
    });
  }
}

//adminDashboardCon.ts
// GET /onsite
export async function getCurrentlyOnsiteController(req: Request, res: Response) {
  try {
    const rawData = await fetchCurrentlyOnsite();
    const normalizedData = rawData.map((item: any) => ({
      ...item,
      profiles: Array.isArray(item.profiles) ? item.profiles[0] : item.profiles,
    }));
    const formatted = formatOnsite(normalizedData);

    return res.status(200).json({
      status: "success",
      data: formatted,
    });
  } catch (error) {
    return res.status(500).json({
      status: "error",
      message: "Failed to fetch currently onsite staff",
    });
  }
}