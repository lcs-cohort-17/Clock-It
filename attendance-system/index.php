<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in (simple session check)
session_start();
if (!isset($_SESSION['user_id'])) {
    // For demo purposes, set a default user
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Admin User';
}

$pageTitle = 'Dashboard - Attendance Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#EEF3F8] font-sans antialiased">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-[#093C5D]">Attendance System</h1>
                    <div class="ml-10 flex space-x-4">
                        <a href="index.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-900 bg-gray-100">Dashboard</a>
                        <a href="history.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-50">History</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-sm text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div x-data="dashboardApp()" x-init="init()" x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-2 text-sm text-gray-600">Overview of attendance statistics</p>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Employees</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.totalEmployees">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Present Today</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.presentToday">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Late Today</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.lateToday">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Absent Today</p>
                            <p class="text-2xl font-semibold text-gray-900" x-text="stats.absentToday">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Attendance</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="record in recentRecords" :key="record.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900" x-text="record.employeeName"></div>
                                        <div class="text-sm text-gray-500" x-text="record.department"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(record.date)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="record.checkInTime || '—'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="record.checkOutTime || '—'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(record.status)" x-text="record.status"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        function dashboardApp() {
            return {
                stats: {
                    totalEmployees: 0,
                    presentToday: 0,
                    lateToday: 0,
                    absentToday: 0
                },
                recentRecords: [],
                
                async init() {
                    await this.loadData();
                },
                
                async loadData() {
                    try {
                        const response = await fetch('api/attendance.php?action=dashboard');
                        const data = await response.json();
                        this.stats = data.stats;
                        this.recentRecords = data.recentRecords;
                    } catch (error) {
                        console.error('Error loading data:', error);
                    }
                },
                
                formatDate(dateStr) {
                    return new Date(dateStr).toLocaleDateString();
                },
                
                getStatusClass(status) {
                    const classes = {
                        'Present': 'bg-green-100 text-green-800',
                        'Absent': 'bg-red-100 text-red-800',
                        'Late': 'bg-yellow-100 text-yellow-800',
                        'Half Day': 'bg-blue-100 text-blue-800',
                        'Holiday': 'bg-purple-100 text-purple-800'
                    };
                    return classes[status] || 'bg-gray-100 text-gray-800';
                }
            }
        }
    </script>
</body>
</html>