// Sidebar functions
function openSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth >= 992) {
        // Desktop: remove collapsed state
        sidebar.classList.remove('sidebar-collapsed');
        document.body.classList.remove('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', 'false');
    } else {
        // Mobile: show sidebar
        sidebar.classList.remove('-translate-x-full');
        document.body.classList.add('sidebar-open');
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth >= 992) {
        // Desktop: add collapsed state
        sidebar.classList.add('sidebar-collapsed');
        document.body.classList.add('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', 'true');
    } else {
        // Mobile: hide sidebar
        sidebar.classList.add('-translate-x-full');
        document.body.classList.remove('sidebar-open');
    }
}

// SIDEBAR TOGGLE
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    
    if (window.innerWidth >= 992) {
        // Desktop: toggle collapsed state
        if (sidebar.classList.contains('sidebar-collapsed')) {
            sidebar.classList.remove('sidebar-collapsed');
            document.body.classList.remove('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'false');
        } else {
            sidebar.classList.add('sidebar-collapsed');
            document.body.classList.add('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'true');
        }
    } else {
        // Mobile: toggle sidebar visibility
        if (document.body.classList.contains('sidebar-open')) {
            document.body.classList.remove('sidebar-open');
        } else {
            document.body.classList.add('sidebar-open');
        }
    }
}

// Initialize sidebar state on page load
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    
    // Restore desktop sidebar state from localStorage, default to collapsed (hidden)
    if (window.innerWidth >= 992) {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') !== 'false';
        if (isCollapsed) {
            sidebar.classList.add('sidebar-collapsed');
            document.body.classList.add('sidebar-collapsed');
        }
    }
    
    // Add click handler to toggle button
    const toggleBtn = document.querySelector('[data-sidebar-toggle]');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }
    
    // Close mobile sidebar when clicking backdrop
    const backdrop = document.querySelector('.sidebar-backdrop');
    if (backdrop) {
        backdrop.addEventListener('click', function() {
            document.body.classList.remove('sidebar-open');
        });
    }
});