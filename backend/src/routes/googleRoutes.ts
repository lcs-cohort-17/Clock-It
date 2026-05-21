import {Router} from 'express';
import { checkStatus, initiateAuth, handleCallback, disconnect } from '../controllers/googleController.ts';
// import { authMiddleware } from '../middleware/authMiddleware';


const router = Router();

//REQUIRING LOGGED IN ADMIN PROFILE
// router.get('/status',      authMiddleware, checkStatus);
// router.get('/auth',        authMiddleware, initiateAuth);
// router.delete('/disconnect', authMiddleware, disconnect);

// router.get('/callback', handleCallback);
export default router;