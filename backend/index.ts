import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import profileRoutes from './src/routes/profileRoutes.js'
dotenv.config()
const app = express()
app.use(cors())
app.use(express.json())

const port = process.env.PORT || 4321

app.use('/profiles', profileRoutes)
app.listen(port, () => {
  console.log(`http://localhost:${port}`)
})