# Dashboard Tests

These tests cover the canonical staff dashboard under `phpfrontend/src/views`.

## Covered Components

- `staff/staff-dashboard.php`: shared shell and canonical dashboard grid include.
- `partials/DashboardGrid.php`: staff status, activity, profile, calendar, and leave actions.
- `partials/header.php`: mobile navigation and theme controls.
- `partials/staff_sidebar.php`: portal routes, identity block, and sign out.
- `modals/CalendarModal.php`: Alpine calendar state and Bootstrap Icons.
- `modals/LeaveRequestModal.php`: responsive leave-request form.

## Run

```bash
vendor/bin/phpunit tests
```

The suite uses the root `phpunit.xml` configuration.
