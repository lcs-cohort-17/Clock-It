# Clock-It: Attendance and Staff Management System

Clock-It is a responsive, web-based attendance and staff management application designed to track employee check-in and check-out events, visualize historical logs, and manage user accounts. It utilizes a PHP-based routing architecture, an SQLite database backend, and a dynamic Alpine.js frontend framework.

## Project Structure

The project is organized into two primary layers:
* phpbackend: Contains the database schema, configuration, models, controllers, and tests for core logic, authentication, session tokens, and database integrations.
* phpfrontend: Houses the user interface views, layout files, public assets (CSS and JavaScript), and local storage data maps.

Key Directories:
* public/: The document root for serving public web traffic. Contains the entry-point index.php, assets directory for styles and scripts, and routing definitions.
* phpfrontend/src/views/: Contains page layouts and components (such as staff dashboard grid, calendar modal, sidebar, header, and user management screens).
* phpfrontend/src/Data/: Contains mock data profiles and fallback arrays used during testing and offline operation.
* phpbackend/src/config/: Stores database initialization logic (Database.php) and schema setups.

---

## Core Features

### 1. Staff Dashboard
The dashboard provides a central view of daily attendance metrics:
* Total employees registered.
* Clocked-in staff count.
* Staff currently onsite.
* Pending sync operations.
* Real-time list of onsite staff and recent clock-in activity.
* CSV export functionality for onsite reports.

### 2. QR Code Scanner (Clock-In/Clock-Out)
Employees can log attendance by scanning QR codes:
* Environment and user-facing camera support using Html5Qrcode.
* Simulated backup actions (Demo: Clock In and Demo: Clock Out buttons) for testing without a webcam.
* Instant feedback and automatic synchronization with the SQLite database.

### 3. Attendance History
Provides a thorough view of an employee's historic records:
* Toggle between a paginated List View and an interactive Calendar View.
* Metrics section showing Total Days, Present percentage, Late percentage, Absent percentage, and Average Working Hours.
* Search by date or status.
* Multi-field sorting (Date, Check In, Check Out, Working Hours, Status).
* Filters for time periods (All Time, This Week, This Month) and statuses.
* Data export to CSV format.

### 4. Admin User Management
Administrators can perform full user lifecycle management:
* Add new employees with auto-generated temporary passwords.
* Toggle active status to enable or disable accounts.
* Edit user profile details (Name, Email, Employee ID, Role).
* Reset user passwords with local logging of welcome and temporary credentials.
* Duplicate email validation handling to prevent duplicate SQL constraint violations.

---

## Database Configuration

The application uses an SQLite database file. The connection singleton is managed by the class \Config\Database inside phpbackend/src/config/Database.php.

The database tables are:
* users: Stores credentials, roles, email, active status, and temporary login flags.
* sessions: Stores profile_id, clock_in_time, clock_out_time, device info, and location.

---

## Installation and Local Setup

### Prerequisites
* PHP 8.2 or higher.
* Composer (for PHP dependency management).
* SQLite extension enabled in php.ini.

### Step-by-Step Run Instructions
1. Clone the repository and navigate to the project directory:
   ```bash
   cd Clock-It
   ```
2. Start the built-in PHP development server using the router.php script. This script acts as an asset path bypass and directs request traffic through the index file:
   ```bash
   php -S localhost:8000 router.php
   ```
   If PHP is not in your system environment path, run the command pointing directly to your installation directory (e.g. on Windows with XAMPP):
   ```bash
   C:\xampp\php\php.exe -S localhost:8000 router.php
   ```
3. Open your web browser and navigate to:
   ```text
   http://localhost:8000
   ```

---

## Developer Workflow and Git Guidelines

To maintain code quality and prevent branch synchronization issues, all developers must strictly adhere to the following workflow.

### 1. Branch Naming Conventions
Always create feature branches off an up-to-date main branch. Do not push directly to main.

* Frontend Features: Use the format `your-name/frontend/ticket-title`
  Example: `jake/frontend/sidebar-icons`
* Backend Features: Use the format `your-name/backend/ticket-title`
  Example: `jacob/backend/auth-middleware`
* Scrum Master Cleanup: Use the format `your-name/SM-team/branch-purpose`
  Example: `sarah/SM-team/repo-cleanup`
* Bug Fixes: Use the format `bugfix/ticket-title`
  Example: `bugfix/vite-config-crash`

### 2. Daily Commands Loop
Before you begin editing files:
```bash
git checkout main
git pull origin main
```

Create your new feature branch:
```bash
git checkout -b your-name/frontend/sidebar-icons
```

The first time you push your branch to GitHub, set the upstream reference:
```bash
git push -u origin HEAD
```

For subsequent routine commits and pushes:
```bash
git add .
git commit -m "feat: short description of change"
git push
```

### 3. Submission and Merging
1. Create a Pull Request on GitHub from your feature branch to main.
2. Use the official Pull Request Details template to write your summary.
3. Assign reviewers on the right panel:
   * For Frontend tasks: tag `@lcs-cohort-17/frontend-sm`
   * For Backend tasks: tag `@lcs-cohort-17/backend-sm`
4. The Scrum Master will pull and test your code locally before approving and merging.

---

## Running Automated Tests

A comprehensive suite of automated tests is provided to verify both frontend and backend functionality.

To execute the frontend PHPUnit test suite, run the following command from the root directory:
```bash
C:\xampp\php\php.exe vendor/bin/phpunit --bootstrap phpfrontend/tests/bootstrap.php phpfrontend/tests
```
All tests should pass successfully before you submit a Pull Request.
