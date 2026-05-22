import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import profileRoutes from './src/routes/profileRoutes.js'

dotenv.config()

const app = express()
app.use(cors())
app.use(express.json())

const port = process.env.PORT || 4321

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