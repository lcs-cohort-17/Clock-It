<div class="d-inline-flex align-items-center p-1 bg-light rounded-xl">
    <a 
        href="?view=list&filter=<?= urlencode($currentFilter) ?>" 
        class="btn btn-sm border-0 px-3 py-1.5 small rounded-lg transition <?= $currentView === 'list' ? 'bg-white text-dark shadow-sm fw-medium' : 'text-muted' ?>"
    >
        List view
    </a>
    <a 
        href="?view=calendar&filter=<?= urlencode($currentFilter) ?>" 
        class="btn btn-sm border-0 px-3 py-1.5 small rounded-lg transition <?= $currentView === 'calendar' ? 'bg-white text-dark shadow-sm fw-medium' : 'text-muted' ?>"
    >
        Calendar view
    </a>
</div>