import express from 'express'
import {
  adminGettingAllUsersCon,
  adminCreatingUserCon,
  loginProfileCon,
  adminUpdatingUserCon,
  adminDeletingUserCon,
  updatePasswordCon,
  resetPasswordCon,
  getProfileByIdCon
} from '../controllers/profileController.js'

import { authenticateToken } from '../middleware/authMiddleware.js'

const router = express.Router()

// Public routes
router.post('/login', loginProfileCon)

// Protected routes
router.get('/', authenticateToken, adminGettingAllUsersCon)
router.get('/:employee_id', authenticateToken, getProfileByIdCon)
router.post('/', authenticateToken, adminCreatingUserCon)
router.patch('/:employee_id', authenticateToken, adminUpdatingUserCon)
router.delete('/:employee_id', authenticateToken, adminDeletingUserCon)
router.patch('/:employee_id/update-password', authenticateToken, updatePasswordCon)
router.patch('/:employee_id/reset-password', authenticateToken, resetPasswordCon)

export default router