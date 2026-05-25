import express from "express";
import rateLimit from "express-rate-limit";
import type { SupabaseClient } from "@supabase/supabase-js";
import { handleGetStats } from "../controllers/adminDashboardController.ts";
import { getRecentActivityController, getCurrentlyOnsiteController } from '../controllers/adminDashboardCon.js';

const router = express.Router();

export function buildAdminDashboardRouter(supabase: SupabaseClient) {
  const statsRateLimiter = rateLimit({
    windowMs: 1_000,
    max: 1,
    standardHeaders: true,
    legacyHeaders: false,
    message: { error: "Too many requests. Max 1 request per second." },
  });
1
  router.get("/stats", statsRateLimiter, handleGetStats(supabase));

  router.use((req, res) => {
    res.status(404).json({ error: "Not found" });
  });

  return router
}

//Get Recent Activity and Onsite Staff routes

router.get('/recent-activity', getRecentActivityController);
router.get('/onsite', getCurrentlyOnsiteController);
export default router;

