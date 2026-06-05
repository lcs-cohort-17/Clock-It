<?php

return [
    'application_name'  => 'Clock It Attendance',
    'spreadsheet_scope' => [
        Google\Service\Sheets::SPREADSHEETS,
        Google\Service\Drive::DRIVE_FILE,
    ],
    'credentials_path'  => __DIR__ . '/../../credentials.json',
    'spreadsheet_id'    => '1n0cv8WVQuM4RR0jMU85dZe_aWxFYNDU_k6YNjdKG7I0',
];
