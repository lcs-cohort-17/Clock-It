import { Router } from 'express';
import {
  checkStatus,
  initiateAuth,
  handleCallback,
  disconnect,
} from '../controllers/googleController.ts';
// import { authMiddleware } from '../middleware/authMiddleware.ts';

const router = Router();

// ── Public (no auth) ──────────────────────────────────────────
// Google redirects here after OAuth — user is not logged in at this point
router.get('/callback', handleCallback);

// ── Protected (swap comments once authMiddleware is ready) ───
// router.get('/status',         authMiddleware, checkStatus);
// router.get('/auth',           authMiddleware, initiateAuth);
// router.delete('/disconnect',  authMiddleware, disconnect);

// Temporary: unguarded while authMiddleware is pending
router.get('/status', checkStatus);
router.get('/auth', initiateAuth);
router.delete('/disconnect', disconnect);

export default router;