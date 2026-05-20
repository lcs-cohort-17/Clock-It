/* lutfeeya / adminDashboard */

import { useEffect, useState, type FC } from 'react';
import { Users, MapPin, AlertCircle, Calendar, QrCode, FileText, Settings, FileSpreadsheet, RefreshCw, ChevronRight } from 'lucide-react';
import Sidebar from '../components/adminDashSidebar';
import TopNav from '../components/adminDashTopNav';
import {
  dashboardStats,
  recentEvents,
  currentlyOnsiteStaff,
  type DashboardStats,
} from '../adminDashMockData/dashboardMock';

const formatWeekday = (date: Date) =>
  new Intl.DateTimeFormat('en-ZA', { weekday: 'long' }).format(date);

const formatTime = (date: Date) =>
  new Intl.DateTimeFormat('en-ZA', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).format(date);

const formatDayMonth = (date: Date) =>
  new Intl.DateTimeFormat('en-ZA', {
    day: '2-digit',
    month: 'short',
  }).format(date);

const AdminDashboard: FC = () => {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  const stats: DashboardStats = dashboardStats;

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 1000);

    return () => clearTimeout(timer);
  }, []);

  const statCards = [
    { label: 'Currently onsite', value: stats.currentlyOnsite, icon: MapPin, color: 'bg-olive/10 text-olive', subtitle: 'Live count, updates within seconds', live: true },
    { label: 'Total clocked in today', value: stats.totalStaffToday, icon: Users, color: 'bg-olive/10 text-olive', subtitle: formatWeekday(new Date()), live: false },
    { label: 'Pending sync', value: stats.pendingSync, icon: AlertCircle, color: stats.pendingSync > 0 ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-500', subtitle: stats.pendingSync > 0 ? 'Need sync' : 'All synced', live: false },
    { label: 'Total events today', value: stats.totalEvents, icon: Calendar, color: 'bg-navy/10 text-navy', subtitle: 'In + Out', live: false },
  ];

  return (
    <div className="flex h-screen bg-background">
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
      
      <div className="flex-1 flex flex-col overflow-hidden lg:ml-64">
        <TopNav onMenuClick={() => setSidebarOpen(true)} />
        
        <main className="flex-1 overflow-y-auto p-4 lg:p-6">
          <div className="max-w-7xl mx-auto space-y-6">
            {/* Header with Actions */}
            <div className="flex flex-wrap items-center justify-between gap-3">
              <div>
                <h1 className="text-2xl font-bold text-navy">Admin Dashboard</h1>
                <p className="text-gray-500 text-sm">Live overview of your team's attendance.</p>
              </div>
              <div className="flex gap-2">
                <button
                  className="btn-outline flex items-center gap-2 disabled:cursor-not-allowed disabled:opacity-60"
                  type="button"
                  onClick={() => setIsLoading(true)}
                  
                >
                  <RefreshCw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
                  {isLoading ? 'Syncing' : 'Sync now'}
                </button>
                <button className="btn-primary flex items-center gap-2">
                  <FileSpreadsheet className="w-4 h-4" />
                  Export to Sheets
                </button>
              </div>
            </div>

          
          {isLoading ? (
              <div className="space-y-6">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                  {Array.from({ length: 4 }).map((_, index) => (
                    <div key={index} className="card animate-pulse">
                      <div className="mb-4 h-10 w-10 rounded-xl bg-slate-200" />
                      <div className="mb-3 h-8 w-16 rounded bg-slate-200" />
                      <div className="mb-2 h-4 w-32 rounded bg-slate-200" />
                      <div className="h-3 w-40 rounded bg-slate-100" />
                    </div>
                  ))}
                </div>
                <div className="card animate-pulse">
                  <div className="mb-4 h-5 w-40 rounded bg-slate-200" />
                  <div className="space-y-3">
                    {Array.from({ length: 4 }).map((_, index) => (
                      <div key={index} className="flex items-center justify-between border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                        <div className="flex items-center gap-3">
                          <div className="h-10 w-10 rounded-full bg-slate-200" />
                          <div>
                            <div className="mb-2 h-4 w-32 rounded bg-slate-200" />
                            <div className="h-3 w-20 rounded bg-slate-100" />
                          </div>
                        </div>
                        <div className="h-4 w-16 rounded bg-slate-200" />
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            ) : (
              <>
            {/* Stats Grid - 4 columns */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              {statCards.map((stat, idx) => {
                const Icon = stat.icon;
                return (
                  <div key={idx} className="card hover:shadow-md transition-shadow">
                    <div className="flex items-start justify-between mb-3">
                      <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${stat.color}`}>
                        <Icon className="w-5 h-5" />
                      </div>
                      {stat.live && (
                        <div className="flex items-center gap-1.5">
                          <div className="w-2 h-2 bg-olive rounded-full animate-pulse" />
                          <span className="text-xs text-gray-400">Live</span>
                        </div>
                      )}
                    </div>
                    <div className="text-3xl font-bold text-navy mb-1">{stat.value}</div>
                    <div className="font-medium text-gray-700 mb-1">{stat.label}</div>
                    <div className="text-xs text-gray-400">{stat.subtitle}</div>
                  </div>
                );
              })}
            </div>

            {/* Bottom Section - 3 cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {/* QR Generator Card */}
              <div className="card card-hover cursor-pointer">
                <div className="flex items-start justify-between mb-3">
                  <div className="w-10 h-10 bg-blue/10 rounded-xl flex items-center justify-center">
                    <QrCode className="w-5 h-5 text-blue" />
                  </div>
                  <ChevronRight className="w-4 h-4 text-gray-400" />
                </div>
                <h3 className="font-semibold text-navy mb-1">QR Generator</h3>
                <p className="text-gray-500 text-sm">Create and manage clock-in QR codes</p>
              </div>

              {/* Attendance Logs Card */}
              <div className="card card-hover cursor-pointer">
                <div className="flex items-start justify-between mb-3">
                  <div className="w-10 h-10 bg-olive/10 rounded-xl flex items-center justify-center">
                    <FileText className="w-5 h-5 text-olive" />
                  </div>
                  <ChevronRight className="w-4 h-4 text-gray-400" />
                </div>
                <h3 className="font-semibold text-navy mb-1">Attendance Logs</h3>
                <p className="text-gray-500 text-sm">All clock events with full audit trail</p>
              </div>

              {/* Settings Card */}
              <div className="card card-hover cursor-pointer">
                <div className="flex items-start justify-between mb-3">
                  <div className="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                    <Settings className="w-5 h-5 text-navy" />
                  </div>
                  <ChevronRight className="w-4 h-4 text-gray-400" />
                </div>
                <h3 className="font-semibold text-navy mb-1">Settings</h3>
                <p className="text-gray-500 text-sm">Manage users and integration</p>
              </div>
            </div>

            {/* Currently Onsite Staff List */}
            <div className="card">
              <div className="flex items-center justify-between mb-4">
                <div>
                  <h3 className="font-semibold text-navy">Currently onsite</h3>
                  <p className="text-gray-500 text-xs">Live count, updates within seconds</p>
                </div>
                <div className="flex items-center gap-1.5">
                  <div className="w-2 h-2 bg-olive rounded-full animate-pulse" />
                  <span className="text-xs text-gray-400">Live</span>
                </div>
              </div>
              
              {stats.currentlyOnsite === 0 ? (
                <div className="text-center py-8">
                  <p className="text-gray-500 text-sm">No staff currently onsite.</p>
                </div>
              ) : (
                <div className="space-y-3">
                  {currentlyOnsiteStaff.map(user => (
                    <div key={user.id} className="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                          <span className="text-navy font-medium text-sm">
                            {user.name.split(' ').map(n => n[0]).join('')}
                          </span>
                        </div>
                        <div>
                          <p className="text-sm font-medium text-gray-700">{user.name}</p>
                          <p className="text-xs text-gray-400">{user.employeeId}</p>
                        </div>
                      </div>
                      <div className="text-right">
                        <div className="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-olive/10">
                          <div className="w-1.5 h-1.5 bg-olive rounded-full" />
                          <span className="text-xs text-olive font-medium">Onsite</span>
                        </div>
                        <p className="text-xs text-gray-400 mt-1">
                          since {formatTime(new Date(user.clockedInAt))}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Recent Activity / Attendance Logs Preview */}
            <div className="card">
              <div className="flex items-center justify-between mb-4">
                <h3 className="font-semibold text-navy">Recent Activity</h3>
                <button className="text-blue text-sm hover:underline">View all &gt;</button>
              </div>
              <div className="space-y-3">
                {recentEvents.map(event => (
                  <div key={event.id} className="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <div className="flex items-center gap-3">
                      <div className={`w-2 h-2 rounded-full ${event.type === 'in' ? 'bg-olive' : 'bg-red-400'}`} />
                      <div>
                        <p className="text-sm font-medium text-gray-700">{event.userName}</p>
                        <p className="text-xs text-gray-400 capitalize">{event.type}</p>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="text-sm text-gray-600">{formatTime(new Date(event.timestamp))}</p>
                      <p className="text-xs text-gray-400">{formatDayMonth(new Date(event.timestamp))}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Sheets Integration Card */}
            <div className="card">
              <div className="flex items-center gap-3 mb-3">
                <div className="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                  <FileSpreadsheet className="w-5 h-5 text-red-500" />
                </div>
                <h3 className="font-semibold text-navy">Sheets Integration</h3>
              </div>
              <p className="text-gray-500 text-sm mb-4">Sync attendance data to Google Sheets</p>
              <div className="flex items-center justify-between">
                <span className="text-red-500 text-sm font-medium">Not connected</span>
                <button className="px-3 py-1.5 bg-gray-100 rounded-lg text-gray-700 text-sm hover:bg-gray-200 transition">
                  Connect
                </button>
              </div>
            </div>
              </>
            )}
          </div>
        </main>
      </div>
    </div>
  );
};

export default AdminDashboard;

/* lutfeeya / adminDashboard */