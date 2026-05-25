//routes/adminDashboard.routes.ts
//routes/adminDashboard.routes.ts

import express from 'express';
import { getRecentActivityController, getCurrentlyOnsiteController } from '../controllers/adminDashboardCon.js';

const router = express.Router();

//Get Recent Activity and Onsite Staff routes
router.get('/recent-activity', getRecentActivityController);
router.get('/onsite', getCurrentlyOnsiteController);

export default router;
