import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import { createClient } from '@supabase/supabase-js'
import { buildAdminDashboardRouter } from './adminDashboard.js'

dotenv.config()
const app = express()
app.use(cors())
app.use(express.json())

const port = process.env.PORT || 4321
const supabaseUrl = process.env.SUPABASE_URL
const supabaseKey = process.env.SUPABASE_KEY || process.env.SUPABASE_ANON_KEY

if (!supabaseUrl || !supabaseKey) {
  throw new Error('Missing SUPABASE_URL or SUPABASE_KEY/SUPABASE_ANON_KEY in environment')
}

const supabase = createClient(supabaseUrl, supabaseKey)

app.use('/api/admin/dashboard', buildAdminDashboardRouter(supabase))

app.listen(port, () => {
  console.log(`http://localhost:${port}`)
})