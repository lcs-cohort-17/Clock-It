<?php
/**
 * Header Component
 * Main page header with branding and welcome message
 */
?>
<header class="app-header d-flex justify-content-between align-items-center px-4 py-3 bg-white" style="border-bottom: 1px solid rgba(9, 60, 93, 0.06); min-height: 72px;">
    
    <div class="d-flex align-items-center gap-2">
        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 32px; height: 32px; background-color: rgba(9, 60, 93, 0.04);">
            <i class="bi bi-clock-history fs-5" style="color: var(--mid-blue, #3B7597);"></i>
        </div>
        <h4 class="m-0 fw-bold tracking-tight" style="color: var(--deep-navy, #093C5D); font-size: 1.2rem; letter-spacing: -0.3px;">
            Clock-It
        </h4>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="text-end d-none d-sm-block">
            <span class="text-muted d-block font-monospace" style="font-size: 0.7rem; letter-spacing: 0.8px; font-weight: 700;">
                ACTIVE SESSION
            </span>
            <span class="fw-semibold" style="color: var(--deep-navy, #093C5D); font-size: 0.9rem;">
                Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
            </span>
        </div>
        
        <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; background-color: var(--light-gray, #F5F5F5); border: 1px solid rgba(9, 60, 93, 0.08);">
            <i class="bi bi-person-fill opacity-75" style="color: var(--mid-blue, #3B7597); font-size: 1.1rem;"></i>
        </div>
    </div>
    
</header>