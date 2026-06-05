<?php
$options = [
    'week' => 'This week',
    'month' => 'This month',
    'all' => 'All time'
];
$currentLabel = $options[$currentFilter] ?? 'All time';
?>

<div class="dropdown">
    <button 
        class="btn btn-white border border-light-subtle rounded-xl px-3 py-2.5 d-flex align-items-center justify-content-between shadow-sm bg-white text-dark dropdown-toggle" 
        type="button" 
        id="filterDropdown" 
        data-bs-toggle="dropdown" 
        aria-expanded="false"
        style="width: 160px; font-size: 0.875rem; font-weight: 500;"
    >
        <?= $currentLabel ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end rounded-xl shadow border-0 py-1 mt-2" aria-labelledby="filterDropdown" style="min-width: 160px;">
        <?php foreach ($options as $value => $label): ?>
            <li>
                <a 
                    class="dropdown-item py-2 px-3 small <?= $currentFilter === $value ? 'bg-light text-dark fw-medium' : 'text-secondary' ?>" 
                    href="?filter=<?= $value ?>&view=<?= urlencode($currentView) ?>"
                >
                    <?= $label ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>