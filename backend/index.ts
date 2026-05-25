import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import { buildAdminDashboardRouter } from './src/routes/adminDashboardRoutes.ts'
import { supabase } from './src/config/supabase.ts'

import attendanceRoutes from './src/routes/leaveRoutes.js'
import profileRoutes from './src/routes/profileRoutes.js'

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

app.use('/api/leaves', attendanceRoutes)

const port = process.env.PORT || 4321

app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))
app.use((req, res, next) => {
  console.log(`[DEBUG] ${req.method} ${req.url}`)
  next()
})

// Test route
app.get('/test', (req, res) => res.json({ message: 'Test route works!' }))

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