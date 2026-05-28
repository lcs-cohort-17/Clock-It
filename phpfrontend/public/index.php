/**router file */

<?php

$title = 'User Management';

$view = __DIR__ . '/../src/views/admin/usermanagement.php';

ob_start();

include $view;

$content = ob_get_clean();


include __DIR__ . '/../src/views/layouts/app.php';