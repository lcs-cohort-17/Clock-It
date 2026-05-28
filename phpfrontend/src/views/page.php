<?php

declare(strict_types=1);

/**
 * @var array{title: string, description: string} $route
 */
?>
<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-4 p-lg-5">
    <a class="btn btn-link px-0 mb-4 text-decoration-none" href="/">&larr; Back to dashboard</a>
    <h1 class="display-6 fw-bold mb-3"><?= e($route['title']) ?></h1>
    <p class="lead text-muted mb-0"><?= e($route['description']) ?></p>
  </div>
</div>

