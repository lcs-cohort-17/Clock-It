<!-- <?php
/**
 * Sidebar Component
 * Main navigation sidebar for the application
 */
?>
<aside class="app-sidebar" x-data="{ sidebarOpen: true }">
  <div>
    <h1>Clock-It</h1>
    <p>Attendance System.</p>
  </div>

  <nav class="sidebar-nav">
    <a href="<?= route_url('/staff-dashboard') ?>" class="sidebar-nav-link" title="Dashboard">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
      <span>Dashboard</span>
    </a>

    <a href="<?= route_url('/scan-qr') ?>" class="sidebar-nav-link" title="Scan QR Code">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M3 11h8V3H3v8zm2-6h4v4H5V5zm8-2v8h8V3h-8zm6 6h-4V5h4v4zM3 21h8v-8H3v8zm2-6h4v4H5v-4zm13-2h1v4h-1v-4zm-4 4h4v1h-4v-1zm1-3h1v2h-1v-2z"/></svg>
      <span>Scan QR Code</span>
    </a>

    <a href="<?= route_url('/history') ?>" class="sidebar-nav-link" title="Attendance History">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.46.37.84-1.39-.46-.37L12 13V8h-2z"/></svg>
      <span>Attendance History</span>
    </a>

    <a href="<?= route_url('/calendar') ?>" class="sidebar-nav-link" title="Calendar">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/></svg>
      <span>Calendar</span>
    </a>

    <a href="<?= route_url('/profile') ?>" class="sidebar-nav-link" title="Profile">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
      <span>Profile</span>
    </a>
  </nav>

  <button class="sidebar-logout" x-on:click="logoutUser()" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
    <span>Sign Out</span>
  </button>
</aside>

<script>
function logoutUser() {
  if (confirm('Are you sure you want to sign out?')) {
    window.location.href = '<?= route_url('/logout') ?>';
  }
}
</script> -->
<?php
/**
 * Sidebar Component
 * Main navigation sidebar for the application
 */
?>

<!-- Embedded Component Styles -->
<style>
    /* Design Variables Matching your Profile Design */
    :root {
        --deep-navy: #093C5D;
        --mid-blue: #3B7597;
        --olive-green: #9CB07A;
        --light-gray: #F5F5F5;
    }

    /* Main Container with Grain Gradient Execution */
    .app-sidebar {
        width: 280px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 2rem 1.25rem;
        color: #FFFFFF;
        background: 
            /* Grain Texture Overlap */
            url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.80' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.06'/%3E%3C/svg%3E"),
            /* Base Fluid Gradient */
            linear-gradient(135deg, var(--mid-blue) 0%, var(--deep-navy) 100%);
        box-shadow: 4px 0 30px rgba(9, 60, 93, 0.12);
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        z-index: 1000;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Brand Header Text Block */
    .sidebar-brand h1 {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.5px;
        margin-bottom: 0.15rem;
        color: #FFFFFF;
    }

    .sidebar-brand p {
        font-size: 0.825rem;
        color: rgba(255, 255, 255, 0.6);
        font-weight: 500;
        margin-bottom: 0;
    }

    /* Link Tree Dynamic Sizing */
    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        margin-top: 2.5rem;
        flex-grow: 1;
    }

    /* Anchor Nav Item Blueprint */
    .sidebar-nav-link {
        display: flex;
        align-items: center;
        gap: 0.95rem;
        padding: 0.75rem 1rem;
        color: rgba(255, 255, 255, 0.75);
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 500;
        border-radius: 10px;
        transition: all 0.2s ease-in-out;
    }

    .sidebar-nav-link svg {
        opacity: 0.8;
        transition: transform 0.2s ease;
    }

    /* Interactive Hover Treatment */
    .sidebar-nav-link:hover {
        color: #FFFFFF;
        background-color: rgba(255, 255, 255, 0.08);
    }

    .sidebar-nav-link:hover svg {
        transform: scale(1.05);
        opacity: 1;
    }

    /* Active Page Route Treatment (Uses Olive Green Accent Pop) */
    .sidebar-nav-link.active {
        color: #FFFFFF;
        background-color: var(--olive-green);
        box-shadow: 0 4px 12px rgba(156, 176, 122, 0.25);
    }
    
    .sidebar-nav-link.active svg {
        opacity: 1;
    }

    /* Action Trigger Footer Interface */
    .sidebar-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 1.25rem;
    }

    .sidebar-logout {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 0.95rem;
        padding: 0.75rem 1rem;
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.95rem;
        font-weight: 500;
        text-align: left;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .sidebar-logout:hover {
        color: #FF6B6B; /* Contextual Warning tint on logout focus */
        background-color: rgba(255, 107, 107, 0.08);
    }

    /* Handle state tracking if user implements collapses later */
    [x-cloak] { display: none !important; }
</style>

<aside class="app-sidebar" x-data="{ sidebarOpen: true }" x-show="sidebarOpen">
    <div>
        <!-- Top App Branding -->
        <div class="sidebar-brand ps-2">
            <h1>Clock-It</h1>
            <p>Attendance Management</p>
        </div>

        <!-- Navigation Matrix -->
        <nav class="sidebar-nav">
            <!-- Add class 'active' dynamically based on current page path route -->
            <a href="<?= route_url('/staff-dashboard') ?>" class="sidebar-nav-link active" title="Dashboard">
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
        </nav>
    </div>

    <!-- Bottom Action Area -->
    <div class="sidebar-footer">
        <button class="sidebar-logout" x-on:click="logoutUser()" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
            <span>Sign Out</span>
        </button>
    </div>
</aside>

<script>
function logoutUser() {
    if (confirm('Are you sure you want to sign out?')) {
        window.location.href = '<?= route_url('/logout') ?>';
    }
}
</script>
