import express from 'express'
import cors from 'cors'
import { buildAdminDashboardRouter } from './src/routes/adminDashboardRoutes.ts'
import { supabase } from './src/config/supabase.ts'
const app = express()
app.use(cors())
app.use(express.json())

const port = process.env.PORT || 4321

app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))

app.listen(port, () => {
  console.log(`http://localhost:${port}`)
})