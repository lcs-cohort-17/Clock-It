<div :class="darkMode ? 'bg-[#0f172a] border-slate-800/60' : 'bg-white border-slate-200'" class="flex items-center justify-between w-full px-6 py-4 border-b h-[73px] flex-shrink-0 transition-colors duration-200">
    <div></div>

    <div class="flex items-center space-x-4">
        
        <div :class="darkMode ? 'bg-[#1e293b]/60 border-slate-800' : 'bg-slate-100 border-slate-200'" class="flex items-center rounded-full px-3 py-1.5 border space-x-3 h-9 transition-colors duration-200">
            
            <button 
                @click="darkMode = !darkMode" 
                type="button" 
                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                :class="darkMode ? 'bg-slate-700' : 'bg-amber-400'">
                
                <span 
                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-md transition duration-200 ease-in-out mt-0.5"
                    :class="darkMode ? 'translate-x-[18px]' : 'translate-x-0.5'">
                </span>
            </button>

            <svg :class="darkMode ? 'text-amber-500' : 'text-slate-400'" class="h-4 w-4 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.243 17.657l.707.707M6.343 6.343l.707-.707M14 12a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>

        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 text-xs font-semibold text-emerald-500 dark:text-emerald-400 bg-emerald-500/10 rounded-full border border-emerald-500/20 uppercase tracking-wide">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
                <span>Online</span>
            </span>
            <span :class="darkMode ? 'text-slate-300 bg-slate-800 border-slate-700' : 'text-slate-700 bg-slate-100 border-slate-200'" class="px-3 py-1 text-xs font-semibold rounded-full border uppercase tracking-wide transition-colors duration-200">
                <?= isset($isAdminDashboard) && $isAdminDashboard ? 'Admin' : 'Staff' ?>
            </span>
        </div>
    </div>
</div>