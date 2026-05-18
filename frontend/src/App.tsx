import { useEffect, useState, type FormEvent } from 'react'
import { AuthLogo } from './components/auth/AuthLogo'
import { ConnectionStatus } from './components/auth/ConnectionStatus'
import { DemoAccounts, type DemoAccount } from './components/auth/DemoAccounts'
import { FormTextField } from './components/auth/FormTextField'
// import { PromoPanel } from './components/PromoPanel'

import Header from './components/Header'
import Sidebar from './components/Sidebar'
import DashboardGrid from './components/DashboardGrid' 

function App() {
  return(
    <>
    <Header />
    
    <main className="flex">
    <Sidebar />
    <DashboardGrid />
    </main>
    </>
  
  )
}

export default App  
  