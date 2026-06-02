# Clock-It Quickstart

Clock-It is a PHP-rendered HR and attendance management frontend. It uses Bootstrap, Alpine.js, vanilla JavaScript, localStorage-backed mock state, and no Composer or Node build step.

## Run

```powershell
php -S 127.0.0.1:8000 -t public
```

Open:

```text
http://127.0.0.1:8000/index.php/login
```

## Demo Roles

- Staff: `tentsaolo.khoza@clock-it.local`
- Admin: `admin@clock-it.local`

Any non-empty password works in the frontend demo.

## Main Routes

- `/index.php/admin/dashboard`
- `/index.php/admin/qr`
- `/index.php/admin/attendance`
- `/index.php/admin/users`
- `/index.php/admin/calendar`
- `/index.php/admin/settings`
- `/index.php/staff/dashboard`
- `/index.php/staff/scan-qr`
- `/index.php/staff/history`
- `/index.php/staff/calendar`
- `/index.php/staff/profile`

## State

The PHP router seeds realistic mock users, attendance records, QR tokens, calendar data, and settings. The browser persists edits in localStorage under `clockit.*`, so QR scans, review actions, user edits, and settings remain consistent across pages.
