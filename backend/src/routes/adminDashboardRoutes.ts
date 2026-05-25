import express from "express";
import rateLimit from "express-rate-limit";
import type { SupabaseClient } from "@supabase/supabase-js";
import { handleGetStats } from "../controllers/adminDashboardController.ts";

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

  router.use((req, res) => {
    res.status(404).json({ error: "Not found" });
  });

  return router;
}
