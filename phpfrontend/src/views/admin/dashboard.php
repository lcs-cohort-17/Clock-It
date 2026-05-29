<?php ob_start(); ?>
<h1>Hi all</h1>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>