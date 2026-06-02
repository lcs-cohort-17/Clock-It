# TODO - PHPfrontend / PHPbackend integration

- [x] Update phpbackend `src/public/index.php` to route admin dashboard API endpoints (`/api/admin/recent-activity`, `/api/admin/onsite`, optional `/api/admin/stats`) via `AdminDashboardRouter` / `AdminDashboardController`.
- [x] Replace phpfrontend mock endpoints `public/api/activity.php` and `public/api/onsite.php` with proxies to phpbackend endpoints.

- [ ] Ensure response shapes match what phpfrontend JS expects.
- [ ] Manual test: load admin dashboard and verify recent activity + onsite staff render from backend data.
- [ ] Manual test: check export/sheets endpoints still work.

