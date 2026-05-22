import { StrictMode } from 'react'
<<<<<<< HEAD
import { createRoot } from 'react-dom/client'

import App from './App'
import './index.css'
=======
import ReactDOM from 'react-dom/client'
>>>>>>> fce12ba4e1b5c5f935464309b32a5d9d75d6c432

import App from './App.tsx'
import './index.css'


ReactDOM.createRoot(document.getElementById('root') as HTMLElement).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
