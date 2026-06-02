<?php
// Initialize session context safely if not already loaded globally
if (session_status() !== PHP_SESSION_ACTIVE) { 
    session_start(); 
}

// Extract role matrix securely, defaulting safely to 'staff' if undefined
$userRole = $_SESSION['user_role'] ?? 'staff';
?>

<!-- Opening Tag with Safe Fallback State for Layout Synchronization -->
<aside class="app-sidebar" 
       x-data="{ localOpen: true }" 
       x-init="if (typeof sidebarOpen !== 'undefined') { localOpen = sidebarOpen; $watch('sidebarOpen', value => localOpen = value) }"
       x-show="localOpen" 
       :style="{ width: localOpen ? '16rem' : '0', display: localOpen ? 'flex' : 'none' }">
    
    <div>
        <!-- Top App Branding Matrix -->
        <div class="sidebar-brand ps-2">
            <h1>Clock-It</h1>
            <p><?= $userRole === 'admin' ? 'Administrative Suite' : 'Attendance Portal' ?></p>
        </div>

        <!-- Dynamic Navigation Matrix Handler -->
        <nav class="sidebar-nav">
            
            <?php if ($userRole === 'admin'): ?>
                <!-- ================= ADMIN LOGICAL PRIVILEGES ================= -->
                <a href="<?= route_url('/admin-dashboard') ?>" class="sidebar-nav-link" title="Dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/users') ?>" class="sidebar-nav-link" title="User Management">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0-6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 7c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4zm6 5h-12v-2c0-1.5 3.5-2.5 6-2.5s6 1 6 2.5v2z"/></svg>
                    <span>User Management</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/attendance') ?>" class="sidebar-nav-link" title="Attendance Log">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.46.37.84-1.39-.46-.37L12 13V8h-2z"/></svg>
                    <span>Attendance Log</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/qr-generator') ?>" class="sidebar-nav-link" title="QR Generator">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M3 11h8V3H3v8zm2-6h4v4H5V5zm8-2v8h8V3h-8zm6 6h-4V5h4v4zM3 21h8v-8H3v8zm2-6h4v4H5v-4zm13-2h1v4h-1v-4zm-4 4h4v1h-4v-1zm1-3h1v2h-1v-2z"/></svg>
                    <span>QR Generator</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/settings') ?>" class="sidebar-nav-link" title="Settings">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l1.72-1.35c.19-.15.24-.42.12-.64l-1.63-2.83c-.12-.22-.39-.3-.61-.22l-2.03.81c-.42-.32-.86-.58-1.35-.78L15 2.5c-.04-.25-.25-.43-.5-.43h-3.26c-.25 0-.46.18-.49.43L10.88 5.5c-.48.2-.93.47-1.35.78l-2.03-.81c-.22-.09-.49 0-.61.22L5.25 8.54c-.13.22-.07.49.12.64l1.72 1.35c-.05.3-.07.62-.07.94s.02.64.07.94l-1.72 1.35c-.19.15-.24.42-.12.64l1.63 2.83c.12.22.39.3.61.22l2.03-.81c.42.32.86.58 1.35.78l.32 2.15c.03.25.25.43.5.43h3.26c.25 0 .46-.18.49-.43l.32-2.15c.48-.2.93-.47 1.35-.78l2.03.81c.22.09.49 0 .61-.22l1.63-2.83c.13-.22.07-.49-.12-.64l-1.72-1.35zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                    <span>Settings</span>
                </a>

            <?php else: ?>
                <!-- ================= STAFF STANDARD PRIVILEGES ================= -->
                <a href="<?= route_url('/staff-dashboard') ?>" class="sidebar-nav-link" title="Dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="<?= route_url('/scan-qr') ?>" class="sidebar-nav-link" title="Scan QR Code">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M3 11h8V3H3v8zm2-6h4v4H5V5zm8-2v8h8V3h-8zm6 6h-4V5h4v4zM3 21h8v-8H3v8zm2-6h4v4H5v-4zm13-2h1v4h-1v-4zm-4 4h4v1h-4v-1zm1-3h1v2h-1v-2z"/></svg>
                    <span>Scan QR Code</span>
                </a>

                <a href="<?= route_url('/history') ?>" class="sidebar-nav-link" title="Attendance History">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.46.37.84-1.39-.46-.37L12 13V8h-2z"/></svg>
                    <span>Attendance History</span>
                </a>

                <a href="<?= route_url('/calendar') ?>" class="sidebar-nav-link" title="Calendar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/></svg>
                    <span>Calendar</span>
                </a>

                <a href="<?= route_url('/profile') ?>" class="sidebar-nav-link" title="Profile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                    <span>Profile</span>
                </a>
            <?php endif; ?>

        </nav>
    </div>

    <!-- Bottom Action Area Container Layout -->
    <div class="sidebar-footer">
        
        <!-- Embedded Cookie-Driven Theme Engine Toggle Component -->
        <div class="theme-toggle-wrapper" x-data="{ isDark: <?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'true' : 'false'; ?> }">
            <button 
                class="theme-toggle-btn" 
                @click="isDark = !isDark; window.themeManager.toggleTheme()"
                :class="isDark ? 'is-dark' : 'is-light'"
                aria-label="Toggle system visual theme"
                type="button"
            >
                <svg 
                    x-show="isDark" 
                    x-cloak
                    xmlns="http://www.w3.org/2000/svg" 
                    viewBox="0 0 24 24" 
                    fill="currentColor" 
                    width="20" 
                    height="20"
                >
                    <path d="M21.64 13a1 1 0 0 0-1.05-.14 8 8 0 1 1 .12-11.85 1 1 0 1 0 1.08-1.63A9.99 9.99 0 0 0 12 2h-.5A9.5 9.5 0 0 0 2 11.5a9.52 9.52 0 0 0 7 9.41 1 1 0 0 0 1.15-.66A1 1 0 0 0 21.64 13z"/>
                </svg>

                <svg 
                    x-show="!isDark" 
                    x-cloak
                    xmlns="http://www.w3.org/2000/svg" 
                    viewBox="0 0 24 24" 
                    fill="currentColor" 
                    width="20" 
                    height="20"
                >
                    <path d="M12 6a1 1 0 0 0 1-1V3a1 1 0 0 0-2 0v2a1 1 0 0 0 1 1zm9-2h-2a1 1 0 0 0 0 2h2a1 1 0 0 0 0-2zM6 12a6 6 0 1 0 6-6 6 6 0 0 0-6 6zm-4 0a1 1 0 0 0-1-1H1a1 1 0 0 0 0 2h2a1 1 0 0 0 1-1zm.22-7a1 1 0 0 0-1.39 1.47l1.44 1.39a1 1 0 0 0 .73.25 1 1 0 0 0 .72-.31 1 1 0 0 0 0-1.41zM17 8.14a1 1 0 0 0 .69-.28l1.44-1.39A1 1 0 0 0 17.78 5a1 1 0 0 0-.72.31 1 1 0 0 0 0 1.41zM12 19a1 1 0 0 0-1 1v2a1 1 0 0 0 2 0v-2a1 1 0 0 0-1-1zm5.73-1.73a1 1 0 0 0-1.39 1.41l1.44 1.39a1 1 0 0 0 .72.31 1 1 0 0 0 .67-.25 1 1 0 0 0 0-1.41zM6.27 17.27a1 1 0 0 0-1.41 0 1 1 0 0 0 0 1.41l1.44 1.39a1 1 0 0 0 .72.31 1 1 0 0 0 .67-.25 1 1 0 0 0 0-1.41zM19 12a1 1 0 0 0-1-1h-2a1 1 0 0 0 0 2h2a1 1 0 0 0 1-1z"/>
                </svg>
            </button>
        </div>

        <!-- System Authentication Terminate Trigger -->
        <button class="sidebar-logout" x-on:click="logoutUser()" type="button" title="Sign Out">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
            <span>Sign Out</span>
        </button>
    </div>
</aside>

<style>
    /* Scoped Theme Toggle Interactive Layout Tokens */
    .theme-toggle-wrapper {
        display: inline-block;
    }

    .theme-toggle-btn {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #FFFFFF;
        border: 1px solid rgba(9, 60, 93, 0.1);
        border-radius: 12px;
        cursor: pointer;
        padding: 0;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(9, 60, 93, 0.04);
    }

    /* Light Mode Specific Adjustments */
    .theme-toggle-btn.is-light {
        color: var(--deep-navy, #093C5D);
    }
    
    .theme-toggle-btn.is-light:hover {
        background-color: var(--light-gray, #F5F5F5);
        border-color: var(--mid-blue, #3B7597);
        color: var(--mid-blue, #3B7597);
        transform: translateY(-1px);
    }

    /* Dark Mode Specific Adjustments */
    .theme-toggle-btn.is-dark {
        color: #F8FAFC;
        background-color: #1E293B;
        border-color: rgba(248, 250, 252, 0.1);
    }

    .theme-toggle-btn.is-dark:hover {
        background-color: #0F172A;
        border-color: rgba(248, 250, 252, 0.2);
        color: var(--olive-green, #9CB07A);
        transform: translateY(-1px);
    }

    .theme-toggle-btn:active {
        transform: translateY(0);
    }

    /* Guard framework style flashing before Alpine initializes */
    [x-cloak] { 
        display: none !important; 
    }
</style>