<?php
function clockit_icon(string $name, string $class = ''): string
{
    $svgs = [
        'clock' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 7v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'mail' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 8l9 6 9-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/></svg>',
        'lock' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="2"/><path d="M7 11V8a5 5 0 0110 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'key' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 21l-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>',
        'user-circle' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 14a4 4 0 100-8 4 4 0 000 8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'wifi' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 13a10 10 0 0114 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M8.5 16.5a5 5 0 017 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="20" r="1.5" fill="currentColor"/></svg>',
        'users' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 19a4 4 0 00-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/><path d="M20 19a3 3 0 00-4-2.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M4 19a3 3 0 014-2.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
        'eye' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>',
        'eye-off' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10.6 10.6A2 2 0 0012 14a2 2 0 001.4-.6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M9.9 5.3A9.5 9.5 0 0112 5c6.5 0 10 7 10 7a18.3 18.3 0 01-3.1 4.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.6 6.6C3.6 8.6 2 12 2 12s3.5 7 10 7a9.7 9.7 0 004.1-.9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ];

    $svg = $svgs[$name] ?? '';
    $classAttr = $class ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
    return '<span' . $classAttr . ' aria-hidden="true">' . $svg . '</span>';
}
