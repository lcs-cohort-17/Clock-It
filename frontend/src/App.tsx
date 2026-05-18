
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
  