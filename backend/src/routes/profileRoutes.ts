import express from 'express'
import {
  getProfilesCon,
  createProfileCon,
  loginProfileCon,
  updateProfileCon,
  deleteProfileCon,
//   resetPasswordCon
} from '../controllers/profileController.js'
import { authenticateToken } from '../middleware/authMiddleware.js'
import { clearCacheController} from '../../src/controllers/cacheController.js'

const router = express.Router()

// Public — no auth needed
router.post('/login', loginProfileCon)

// Protected — must be logged in
router.get('/', getProfilesCon)
router.post('/', createProfileCon)
router.patch('/:employee_id', updateProfileCon)
router.delete('/:employee_id', deleteProfileCon)
router.post('/clear-cache', clearCacheController)
// router.patch('/:employee_id/reset-password', resetPasswordCon)

export default router