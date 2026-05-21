import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
<<<<<<< HEAD
import './index.css'
import App from './App.tsx'
=======

import App from './App'
import './index.css'
>>>>>>> 464480e0fbd76e3ac07190f78f7b627bbbc2a511

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
