<?php
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
</script>
