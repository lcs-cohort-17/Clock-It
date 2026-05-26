process.on('uncaughtException', (err) => {
  console.error('UNCAUGHT EXCEPTION:', err)
  console.error('Stack:', err.stack)
})

process.on('unhandledRejection', (reason, promise) => {
  console.error('UNHANDLED REJECTION at:', promise)
  console.error('Reason:', reason)
})

import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import profileRoutes from './src/routes/profileRoutes.js'
import attendanceRoutes from './src/routes/leaveRoutes.js'
import googleRoutes from './src/routes/googleRoutes.js';
import { buildAdminDashboardRouter } from './src/routes/adminDashboardRoutes.js'
import { supabase } from './src/config/supabase.js'

dotenv.config()

const app = express()
app.use(cors())
app.use(express.json())

//hardedcoded code for auth middleware as I need Amo for her middleware code
app.use((req: any, _res: any, next: any) => {
  req.auth = {
    userId:'07cd9434-1b18-4d96-b029-0595b26d067c',
    role: 'admin',
    token: 'mock-token'
  }
  next()
})


const port = process.env.PORT || 4321

app.use((req, res, next) => {
  console.log(`[DEBUG] ${req.method} ${req.url}`)
  next()
})

// Test route
app.get('/test', (req, res) => res.json({ message: 'Test route works!' }))

app.use('/api/admin', buildAdminDashboardRouter(supabase))
app.use('/api/google', googleRoutes)
app.use('/api/leaves', attendanceRoutes)
app.use('/profiles', profileRoutes)

app.use((req, res) => {
  res.status(404).json({ 
    success: false, 
    error: `Route not found: ${req.method} ${req.url}` 
  })
})
app.listen(port, () => {
  console.log(`🚀 Server running on http://localhost:${port}`)
})

console.log('✅ index.ts loaded successfully')
