// Theme toggle functionality with Tailwind dark mode
function initTheme() {
    const html = document.documentElement;
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = saved || (prefersDark ? 'dark' : 'light');

    if (theme === 'dark') {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }
}

// Function to let your header button manual toggle work cleanly
function toggleTheme() {
    const html = document.documentElement;
    if (html.classList.contains('dark')) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
}

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', initTheme);
initTheme();

// Sidebar functions
function openSidebar() {
    document.getElementById('sidebar')?.classList.remove('-translate-x-full');
}

function closeSidebar() {
    document.getElementById('sidebar')?.classList.add('-translate-x-full');
}

function toggleConnection(button) {
    button.textContent = button.textContent.trim() === 'Connect' ? 'Disconnect' : 'Connect';
}