import express from "express";
import rateLimit from "express-rate-limit";
import type { SupabaseClient } from "@supabase/supabase-js";
import { handleGetStats } from "../controllers/adminDashboardController.ts";
import { exportToSheets } from '../controllers/exportController.ts';

export function buildAdminDashboardRouter(supabase: SupabaseClient) {
  const statsRateLimiter = rateLimit({
    windowMs: 1_000,
    max: 1,
    standardHeaders: true,
    legacyHeaders: false,
    message: { error: "Too many requests. Max 1 request per second." },
  });
  const router = express.Router();

  router.get("/stats", statsRateLimiter, handleGetStats(supabase));

  router.post("/export", exportToSheets);
  
  // Add this new route below your existing routes, before the 404 handler
  // authMiddleware is commented out until the other dev is done
  router.post('/export/sheets', /* authMiddleware, */ exportToSheets);

  router.use((req, res) => {
    res.status(404).json({ error: "Not found" });
  });

  return router;
}