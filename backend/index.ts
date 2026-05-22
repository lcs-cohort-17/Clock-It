import express from 'express'
import cors from 'cors'
import dotenv from 'dotenv'
import attendanceRoutes from './src/routes/leaveRoutes.js'

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

app.listen(port, () => {
  console.log(`http://localhost:${port}`)
})