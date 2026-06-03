import express from 'express'
import {
<<<<<<< HEAD
  getProfilesCon,
  createProfileCon,
  loginProfileCon,
  updateProfileCon,
  deleteProfileCon,
=======
  adminGettingAllUsersCon,
  adminCreatingUserCon,
  loginProfileCon,
  adminUpdatingUserCon,
  adminDeletingUserCon,
>>>>>>> origin/SizaMpafa/SM-team/testing
  updatePasswordCon,
  resetPasswordCon,
  getProfileByIdCon
} from '../controllers/profileController.js'

import { authenticateToken } from '../middleware/authMiddleware.js'

const router = express.Router()

// Public routes
router.post('/login', loginProfileCon)

// Protected routes
<<<<<<< HEAD
router.get('/', authenticateToken, getProfilesCon)
router.get('/:employee_id', authenticateToken, getProfileByIdCon)
router.post('/', authenticateToken, createProfileCon)
router.patch('/:employee_id', authenticateToken, updateProfileCon)
router.delete('/:employee_id', authenticateToken, deleteProfileCon)
=======
router.get('/', authenticateToken, adminGettingAllUsersCon)
router.get('/:employee_id', authenticateToken, getProfileByIdCon)
router.post('/', authenticateToken, adminCreatingUserCon)
router.patch('/:employee_id', authenticateToken, adminUpdatingUserCon)
router.delete('/:employee_id', authenticateToken, adminDeletingUserCon)
>>>>>>> origin/SizaMpafa/SM-team/testing
router.patch('/:employee_id/update-password', authenticateToken, updatePasswordCon)
router.patch('/:employee_id/reset-password', authenticateToken, resetPasswordCon)

export default router