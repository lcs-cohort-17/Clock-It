<?php 
$title = "Clock-It Staff Dashboard";
$isAdminDashboard = false;
ob_start(); 
?>

<?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>

<div :class="darkMode ? 'bg-[#0f172a]' : 'bg-slate-50'" class="flex-1 flex flex-col min-w-0 h-full overflow-hidden transition-colors duration-200">
    
    <?php require __DIR__ . '/../partials/header.php'; ?>

    <main class="flex-1 p-6 lg:p-8 space-y-8 overflow-y-auto">
        
        <div class="space-y-1">
            <h1 :class="darkMode ? 'text-white' : 'text-slate-900'" class="text-3xl font-bold tracking-tight transition-colors duration-200">Staff Dashboard</h1>
            <p :class="darkMode ? 'text-slate-400' : 'text-slate-500'" class="text-sm transition-colors duration-200">Welcome back, Demo Staff.</p>
        </div>
        
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            
            <div :class="darkMode ? 'bg-[#1e293b]/50 border-slate-800/80' : 'bg-white border-slate-200 shadow-sm'" class="p-6 rounded-xl border relative transition-all duration-200">
                <div class="flex items-center justify-between">
                    <p :class="darkMode ? 'text-slate-400' : 'text-slate-500'" class="text-xs font-semibold uppercase tracking-wider transition-colors duration-200">Currently Onsite</p>
                    <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_#10b981]"></span>
                </div>
                <div :class="darkMode ? 'text-white' : 'text-slate-900'" class="text-4xl font-bold tracking-tight mt-4 transition-colors duration-200">2</div>
            </div>

            <div :class="darkMode ? 'bg-[#1e293b]/50 border-slate-800/80' : 'bg-white border-slate-200 shadow-sm'" class="p-6 rounded-xl border transition-all duration-200">
                <div class="flex items-center justify-between">
                    <p :class="darkMode ? 'text-slate-400' : 'text-slate-500'" class="text-xs font-semibold uppercase tracking-wider transition-colors duration-200">Total Staff Today</p>
                </div>
                <div :class="darkMode ? 'text-white' : 'text-slate-900'" class="text-4xl font-bold tracking-tight mt-4 transition-colors duration-200">3</div>
            </div>

            <div :class="darkMode ? 'bg-[#1e293b]/50 border-slate-800/80' : 'bg-white border-slate-200 shadow-sm'" class="p-6 rounded-xl border transition-all duration-200">
                <div class="flex items-center justify-between">
                    <p :class="darkMode ? 'text-slate-400' : 'text-slate-500'" class="text-xs font-semibold uppercase tracking-wider transition-colors duration-200">Pending Sync</p>
                </div>
                <div :class="darkMode ? 'text-white' : 'text-slate-900'" class="text-4xl font-bold tracking-tight mt-4 transition-colors duration-200">0</div>
            </div>

            <div :class="darkMode ? 'bg-[#1e293b]/50 border-slate-800/80' : 'bg-white border-slate-200 shadow-sm'" class="p-6 rounded-xl border transition-all duration-200">
                <div class="flex items-center justify-between">
                    <p :class="darkMode ? 'text-slate-400' : 'text-slate-500'" class="text-xs font-semibold uppercase tracking-wider transition-colors duration-200">Total Events</p>
                </div>
                <div :class="darkMode ? 'text-white' : 'text-slate-900'" class="text-4xl font-bold tracking-tight mt-4 transition-colors duration-200">3</div>
            </div>
            
        </div>

        <div :class="darkMode ? 'bg-[#1e293b]/30 border-slate-800/50' : 'bg-white border-slate-200 shadow-sm'" class="rounded-xl border p-6 space-y-6 transition-all duration-200">
            <h2 :class="darkMode ? 'text-white' : 'text-slate-900'" class="text-xl font-bold tracking-tight transition-colors duration-200">Today's Activity</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr :class="darkMode ? 'border-slate-800/80 text-slate-400' : 'border-slate-100 text-slate-500'" class="border-b text-xs font-semibold uppercase tracking-wider transition-colors duration-200">
                            <th class="pb-3 font-medium">Name</th>
                            <th class="pb-3 font-medium">Type</th>
                            <th class="pb-3 font-medium">Time</th>
                        </tr>
                    </thead>
                    <tbody :class="darkMode ? 'divide-slate-800/30' : 'divide-slate-100'" class="divide-y text-sm">
                        <tr>
                            <td :class="darkMode ? 'text-slate-200' : 'text-slate-700'" class="py-4 font-medium transition-colors duration-200">Demo Staff</td>
                            <td class="py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-blue-500/10 text-blue-500 dark:text-blue-400 border border-blue-500/20 uppercase tracking-wide">
                                    Clock-In
                                </span>
                            </td>
                            <td :class="darkMode ? 'text-slate-400' : 'text-slate-500'" class="py-4 font-mono transition-colors duration-200">2026-05-29 08:00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/app.php'; 
?>