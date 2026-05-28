<!DOCTYPE html>
<html lang="en" 
      x-data="{ darkMode: localStorage.getItem('theme') ? localStorage.getItem('theme') === 'dark' : true }" 
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clock-It Staff Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-slate-900 dark:text-gray-100 min-h-screen transition-colors duration-200">

    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="max-w-7xl mx-auto p-6 md:p-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 dark:border-slate-700 pb-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Staff Dashboard</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your attendance overview.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="/admin-dashboard" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-200 rounded-lg shadow-sm transition-colors duration-150">
                    <svg class="w-4 h-4 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Admin Dashboard
                </a>
            </div>
       </div>
        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-8">
            
            <div class="p-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 relative overflow-hidden transition-all">
                <div class="flex items-center justify-between">
                    <div class="p-3 bg-gray-50 dark:bg-slate-700 text-gray-400 dark:text-gray-300 rounded-lg">
                        <svg class="w-6 h-6 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><circle cx="12" cy="11" r="3"></circle></svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2.5 py-1 rounded-full">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live
                    </span>
                </div>
                <div class="text-5xl font-bold text-gray-900 dark:text-white tracking-tight mt-4">1</div>
                <p class="font-medium text-gray-900 dark:text-gray-200 mt-2">Currently onsite</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Live count, updates within seconds</p>
            </div>

            <div class="p-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 relative overflow-hidden transition-all">
                <div class="flex items-center justify-between">
                    <div class="p-3 bg-gray-50 dark:bg-slate-700 text-gray-400 dark:text-gray-300 rounded-lg">
                        <svg class="w-6 h-6 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 014-4 4 4 0 014 4 4 4 0 01-4 4 4 4 0 01-4-4z"></path></svg>
                    </div>
                </div>
                <div class="text-5xl font-bold text-gray-900 dark:text-white tracking-tight mt-4">2</div>
                <p class="font-medium text-gray-900 dark:text-gray-200 mt-2">Total clocked in today</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tuesday</p>
            </div>
            
        </div>
    </main>

</body>
</html>