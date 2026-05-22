import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import adminRoutes from './routes/adminRoutes.js'

dotenv.config()

export const app = express()
app.use(cors())
app.use(express.json())
app.use('/api/admin', adminRoutes)

const port = process.env.PORT || 4321

if (process.env.NODE_ENV !== 'test') {
  app.listen(port, () => {
    console.log(`http://localhost:${port}`)
  })
}
