import express from 'express'
import {
  getProfilesCon,
  createProfileCon,
  loginProfileCon,
  updateProfileCon,
  deleteProfileCon,
  updatePasswordCon,
  resetPasswordCon,
  getProfileByIdCon
} from '../controllers/profileController.js'

import { authenticateToken } from '../middleware/authMiddleware.js'

const router = express.Router()

// Public routes
router.post('/login', loginProfileCon)

// Protected routes
router.get('/', authenticateToken, getProfilesCon)
router.get('/:employee_id', authenticateToken, getProfileByIdCon)
router.post('/', authenticateToken, createProfileCon)
router.patch('/:employee_id', authenticateToken, updateProfileCon)
router.delete('/:employee_id', authenticateToken, deleteProfileCon)
router.patch('/:employee_id/update-password', authenticateToken, updatePasswordCon)
router.patch('/:employee_id/reset-password', authenticateToken, resetPasswordCon)

export default router