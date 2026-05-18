import { useState } from 'react'
import Header from './components/Header'
import Sidebar from './components/Sidebar'
import DashboardGrid from './components/DashboardGrid'
import { UserProfilePage, type UserProfileData } from './pages/user_profile_section'

type AppView = 'dashboard' | 'profile'

const currentUser: UserProfileData = {
  email: 'sibahle@clockit.app',
  employeeId: 'CLK-014',
  fullName: 'Sibahle',
  role: 'Staff',
}

function App() {
  const [activeView, setActiveView] = useState<AppView>('dashboard')

  return (
    <>
      <Header />

      <main className="flex">
        <Sidebar
          activeView={activeView}
          onDashboardClick={() => setActiveView('dashboard')}
          onProfileClick={() => setActiveView('profile')}
        />
        {activeView === 'profile' ? <UserProfilePage user={currentUser} /> : <DashboardGrid />}
      </main>
    </>
  )
}

export default App
