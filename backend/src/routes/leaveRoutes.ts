import {Router} from 'express';
import {submitLeave,getCalendar,getLeave,updateLeaveStatus,updateLeave} from '../controllers/leaveController.js';
import { validate, submitLeaveSchema, calendarQuerySchema, updateLeaveStatusSchema ,updateLeaveBodySchema} from '../validators/leaveValidator.js';


// TODO: replace this import with the real one from Amo
// import { requireAuth, requireAdmin } from '../middleware/auth'

const router = Router();

// TODO: uncomment these once auth middleware is done 
// router.use(requireAuth)

router.post('/', validate(submitLeaveSchema, 'body'), submitLeave)
router.get('/getCalendar', validate(calendarQuerySchema, 'query'), getCalendar)
router.get('/getLeave', getLeave)
router.patch('/:id/status', validate(updateLeaveStatusSchema, 'body'), updateLeaveStatus)
 router.patch('/:id/updateRequest', validate(updateLeaveBodySchema, 'body'), updateLeave)


export default router;