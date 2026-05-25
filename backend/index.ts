import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import adminDashboardRoutes from './src/routes/adminDashboardRoutes.js'
dotenv.config()
const app = express()
app.use(cors())
app.use(express.json())

const port = process.env.PORT || 4321

app.use('/api/admin/dashboard', adminDashboardRoutes)
app.use('/api/admin/dashboard', adminDashboardRoutes)

app.listen(port, () => {
  console.log(`http://localhost:${port}`)
})