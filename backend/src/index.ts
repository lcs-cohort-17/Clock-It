import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import adminRoutes from './routes/adminRoutes.js' //.js not .ts
console.log('adminRoutes is:', adminRoutes)
dotenv.config()

export const app = express()
app.use(cors())
app.use(express.json())
app.use("/api/admin", adminRoutes)

const port = process.env.PORT || 4321

// Only start server if not running tests
if (process.env.NODE_ENV !== 'test') {
  app.listen(port, () => {
    console.log(`http://localhost:${port}`)
  })
}