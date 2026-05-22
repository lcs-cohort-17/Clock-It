import { createClient } from '@supabase/supabase-js'
import dotenv from 'dotenv'
import path from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
dotenv.config({ path: path.resolve(__dirname, '../../.env') })

const supabaseUrl = process.env.SUPABASE_URL?.trim() // trim() removes whitespace
const supabaseServiceKey = process.env.SUPABASE_SERVICE_KEY?.trim()

console.log('URL length:', supabaseUrl?.length) // should be 46
console.log('URL JSON:', JSON.stringify(supabaseUrl)) // shows hidden chars
console.log('Key exists:', !!supabaseServiceKey)

if (!supabaseUrl || !supabaseServiceKey) {
  throw new Error('Missing SUPABASE_URL or SUPABASE_SERVICE_KEY')
}

export const supabase = createClient(supabaseUrl, supabaseServiceKey)