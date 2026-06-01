<?php ob_start(); /** @var string $phpfrontend_base_url */ ?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Overview of today's attendance and system status.</p>
    </div>
    <div class="action-buttons">
        <button id="sync-btn" class="btn-outline" onclick="syncData()">
            <svg id="sync-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
            <span id="sync-text">Sync now</span>
        </button>
        <button class="btn-primary" onclick="exportData()">Export to Sheets</button>
    </div>
</div>

<!-- Metric Grid -->
<div class="stats-grid" id="stats-container">
    <!-- Card 1: Currently Onsite -->
    <div class="metric-card" id="card-onsite">
        <div class="metric-top">
            <div class="stat-icon icon-navy">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="live-badge">
                <div class="live-dot"></div>
                <span class="live-text">LIVE</span>
            </div>
        </div>
        <div class="stat-value" id="val-onsite">--</div>
        <div class="stat-label">Currently Onsite</div>
        <div class="stat-subtitle">Employees currently clocked in</div>
    </div>

    <!-- Card 2: Total Clocked In -->
    <div class="metric-card" id="card-total-in">
        <div class="metric-top">
            <div class="stat-icon icon-olive">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <div class="stat-value" id="val-total-in">--</div>
        <div class="stat-label">Total Clocked In</div>
        <div class="stat-subtitle">Unique employees active today</div>
    </div>

    <!-- Card 3: Pending Sync -->
    <div class="metric-card" id="card-pending">
        <div class="metric-top">
            <div class="stat-icon icon-gray">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
            </div>
        </div>
        <div class="stat-value" id="val-pending">0</div>
        <div class="stat-label">Pending Sync</div>
        <div class="stat-subtitle">Records awaiting cloud sync</div>
    </div>

    <!-- Card 4: Total Events -->
    <div class="metric-card" id="card-events">
        <div class="metric-top">
            <div class="stat-icon icon-amber">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </div>
        </div>
        <div class="stat-value" id="val-events">--</div>
        <div class="stat-label">Total Events</div>
        <div class="stat-subtitle">Total logs recorded today</div>
    </div>
</div>

<!-- Feature Cards Grid (Small) -->
<div class="feature-grid" style="margin-bottom: 28px;">
    <div class="feature-card" onclick="alert('QR Generator - Coming Soon')">
        <div class="feature-top">
            <div class="feature-icon icon-gray">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <div class="arrow-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>
        <h3>QR Generator</h3>
        <p>Create and manage clock-in QR codes</p>
    </div>

    <div class="feature-card" onclick="alert('Attendance Logs - Coming Soon')">
        <div class="feature-top">
            <div class="feature-icon icon-olive">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="arrow-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>
        <h3>Attendance Logs</h3>
        <p>All clock events with full audit trail</p>
    </div>

    <div class="feature-card" onclick="alert('Settings - Coming Soon')">
        <div class="feature-top">
            <div class="feature-icon icon-navy">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="arrow-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>
        <h3>Settings</h3>
        <p>Manage users and integration</p>
    </div>
</div>

<div class="feature-grid">
    <!-- Staff Onsite List -->
    <div class="large-card">
        <div class="section-header">
            <div class="section-header-left">
                <h3>Currently Onsite</h3>
                <p>Employees active in the building right now.</p>
            </div>
            <button class="view-all-btn">View All</button>
        </div>
        <div id="onsite-list" class="staff-list">
            <!-- Populated by JS -->
            <div class="empty-state">Loading staff list...</div>
        </div>
    </div>

    <!-- Recent Activity List -->
    <div class="large-card" style="grid-column: span 2;">
        <div class="section-header">
            <div class="section-header-left">
                <h3>Recent Activity</h3>
                <p>Latest clock-in/out events across the system.</p>
            </div>
        </div>
        <div id="activity-list" class="activity-list">
            <!-- Populated by JS -->
            <div class="empty-state">Loading activities...</div>
        </div>
    </div>
</div>

<!-- Sheets Integration Card -->
<div class="sheets-card">
    <div class="sheets-content">
        <div class="sheets-left">
            <div class="sheets-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="sheets-info">
                <h3>Sheets Integration</h3>
                <p>Sync attendance data to Google Sheets</p>
            </div>
        </div>
        <div class="sheets-right">
            <div class="connection-status">Not connected</div>
            <button class="connect-btn" onclick="alert('Connect to Google Sheets - Coming Soon')">Connect</button>
        </div>
    </div>
</div>

<script>
let isSyncing = false;

/**
 * Initialize Dashboard
 */
document.addEventListener('DOMContentLoaded', () => {
    refreshAll();
});

/**
 * Global Refresh
 */
async function refreshAll() {
    await Promise.all([
        fetchDashboardStats(),
        fetchOnsiteStaff(),
        fetchRecentActivity()
    ]);
}

/**
 * Sync Data Logic
 */
async function syncData() {
    if (isSyncing) return;
    
    isSyncing = true;
    const btn = document.getElementById('sync-btn');
    const icon = document.getElementById('sync-icon');
    const text = document.getElementById('sync-text');
    
    btn.disabled = true;
    icon.classList.add('animate-spin');
    text.textContent = 'Syncing...';
    
    await refreshAll();
    
    isSyncing = false;
    btn.disabled = false;
    icon.classList.remove('animate-spin');
    text.textContent = 'Sync now';
}

/**
 * Fetch and update dashboard metrics
 */
async function fetchDashboardStats() {
    const cards = ['onsite', 'total-in', 'pending', 'events'];
    const elements = {
        onsite: document.getElementById('val-onsite'),
        totalIn: document.getElementById('val-total-in'),
        pending: document.getElementById('val-pending'),
        events: document.getElementById('val-events')
    };

    // 1. Show Loading State (Skeletons)
    cards.forEach(id => {
        const card = document.getElementById(`card-${id}`);
        card.classList.add('animate-pulse');
        const val = card.querySelector('.stat-value');
        if (val) val.innerHTML = '<div class="skeleton-line" style="width: 40px; margin: 0;"></div>';
    });

    try {
        // Construct the API path using the phpfrontend_base_url passed from PHP
        const apiPath = '<?= $phpfrontend_base_url ?>/api/dashboard-stats.php';
        const response = await fetch(apiPath);
        const result = await response.json();

        if (!result.success) throw new Error(result.error || 'Failed to fetch data');

        // 2. Update Values
        elements.onsite.textContent = result.data.currentlyOnsite;
        elements.totalIn.textContent = result.data.totalClockedInToday;
        elements.pending.textContent = result.data.pendingSync;
        elements.events.textContent = result.data.totalEventsToday;

    } catch (error) {
        console.error('Dashboard Error:', error);
        handleErrorState();
    } finally {
        // 3. Always remove loading state regardless of success or failure
        cards.forEach(id => {
            const card = document.getElementById(`card-${id}`);
            if (card) card.classList.remove('animate-pulse');
        });
    }
}

/**
 * Fetch and update the Onsite Staff list
 */
async function fetchOnsiteStaff() {
    const list = document.getElementById('onsite-list');
    // Show simple loading state
    list.innerHTML = '<div class="empty-state animate-pulse">Updating staff list...</div>';

    try {
        const response = await fetch('<?= $phpfrontend_base_url ?>/api/onsite-staff.php');
        const result = await response.json();
        
        if (!result.success || result.data.length === 0) {
            list.innerHTML = '<div class="empty-state">No staff currently onsite.</div>';
            return;
        }

        list.innerHTML = result.data.map(staff => `
            <div class="staff-item">
                <div class="staff-left">
                    <div class="staff-avatar">${getInitials(staff.name)}</div>
                    <div class="staff-info">
                        <div class="staff-name">${staff.name}</div>
                        <div class="staff-id">${staff.role}</div>
                    </div>
                </div>
                <div class="staff-right">
                    <div class="status-pill"><div class="status-dot"></div> Onsite</div>
                    <div class="staff-time">Since ${new Date(staff.sign_in_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                </div>
            </div>
        `).join('');
    } catch (e) {
        list.innerHTML = '<div class="empty-state">Failed to load staff list.</div>';
    }
}

/**
 * Fetch and update the Recent Activity list
 */
async function fetchRecentActivity() {
    const list = document.getElementById('activity-list');
    list.innerHTML = '<div class="empty-state animate-pulse">Updating activity...</div>';

    try {
        const response = await fetch('<?= $phpfrontend_base_url ?>/api/recent-activity.php');
        const result = await response.json();
        
        if (!result.success || result.data.length === 0) {
            list.innerHTML = '<div class="empty-state">No recent activity found.</div>';
            return;
        }

        list.innerHTML = result.data.map(act => `
            <div class="activity-item">
                <div class="activity-left">
                    <div class="activity-indicator ${act.action}"></div>
                    <div class="activity-info">
                        <div class="activity-name">${act.name}</div>
                        <div class="activity-type">Clocked ${act.action}</div>
                    </div>
                </div>
                <div class="activity-time">
                    <div class="activity-time-value">${new Date(act.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                    <div class="activity-date">${new Date(act.timestamp).toLocaleDateString([], {day: '2-digit', month: 'short'})}</div>
                </div>
            </div>
        `).join('');
    } catch (e) {
        list.innerHTML = '<div class="empty-state">Failed to load activity.</div>';
    }
}

/**
 * Export Data Logic
 */
async function exportData() {
    try {
        const response = await fetch('<?= $phpfrontend_base_url ?>/api/onsite-staff.php');
        const result = await response.json();
        
        if (!result.success || result.data.length === 0) {
            alert('No data to export');
            return;
        }
        
        let csvContent = "Name,Role,Clock In Time\n";
        result.data.forEach(person => {
            const time = new Date(person.sign_in_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            csvContent += `"${person.name}","${person.role}","${time}"\n`;
        });
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `attendance_export_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        alert('Export completed successfully!');
    } catch (error) {
        alert('Failed to export data');
    }
}

/**
 * Render error state in the grid if API fails
 */
function handleErrorState() {
    const container = document.getElementById('stats-container');
    container.innerHTML = `
        <div class="error-card" style="grid-column: 1 / -1;">
            <div class="error-content">
                <div class="error-left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span class="error-message">Failed to load dashboard metrics. Please check your connection.</span>
                </div>
                <button class="retry-btn" onclick="location.reload()">Retry Connection</button>
            </div>
        </div>
    `;
}

/**
 * Helper: Get initials from name
 */
function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
}

// Refresh every 60 seconds
setInterval(refreshAll, 60000);
</script>

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>