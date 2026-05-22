import express from "express";
import {
  getAttendance,
  getLeave,
  getAudit,
  overrideAttendance
} from "../controllers/adminControllers.js";

const router = express.Router();

router.get("/attendance", getAttendance);
router.get("/leave", getLeave);
router.get("/audit", getAudit);
router.patch("/attendance/:id", overrideAttendance);

export default router;