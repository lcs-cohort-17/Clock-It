<?php
// src/routes/google.php

return [
    'GET /api/admin/sheets/status'       => ['GoogleSheetsController', 'status'],
    'POST /api/admin/sheets/export'      => ['GoogleSheetsController', 'export'],
    'POST /api/admin/sheets/sync'        => ['GoogleSheetsController', 'sync'],
    'POST /api/admin/sheets/disconnect'  => ['GoogleSheetsController', 'disconnect'],
];