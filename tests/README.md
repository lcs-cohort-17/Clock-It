# Dashboard Tests

This directory contains comprehensive unit tests for the Dashboard and DashboardGrid components.

## Test Files

### DashboardTest.php
Tests for the main Dashboard.php component, including:
- File existence
- Required component includes (Sidebar, Header, DashboardGrid, Modals)
- Proper HTML5 structure
- CDN dependencies (Bootstrap, Font Awesome, Alpine.js)
- Custom CSS and JavaScript files
- Meta tags and title
- Alpine.js initialization

**Total Tests:** 14

### DashboardGridTest.php
Tests for the DashboardGrid.php component, including:
- File existence
- Main content section structure
- Greeting title and date display
- Bootstrap grid layout
- Status/Clock card with QR scan functionality
- Activity card
- Quick action cards (Calendar, Leave Requests, Profile)
- Font Awesome icon usage
- Alpine.js directives (@click)
- Bootstrap utility classes
- Responsive layout

**Total Tests:** 21

## Setup

### Prerequisites
- PHP 7.4 or higher
- Composer

### Installation

1. Install PHPUnit and dependencies:
```bash
composer install
```

## Running Tests

### Run all tests
```bash
composer test
```

### Run tests with verbose output
```bash
composer test-verbose
```

### Run tests with coverage report
```bash
composer test-coverage
```

This will generate an HTML coverage report in the `coverage/` directory.

### Run specific test file
```bash
./vendor/bin/phpunit tests/DashboardTest.php
./vendor/bin/phpunit tests/DashboardGridTest.php
```

## Test Coverage

The tests verify:

**Dashboard Component:**
- ✓ All required includes are present
- ✓ Valid HTML5 structure
- ✓ All external dependencies are loaded correctly
- ✓ Alpine.js is properly initialized
- ✓ Meta tags for viewport and charset
- ✓ Correct page title

**DashboardGrid Component:**
- ✓ Main section exists with proper IDs
- ✓ Greeting and date display
- ✓ Bootstrap responsive grid layout
- ✓ Status card with current state
- ✓ Activity tracking section
- ✓ Quick action cards for Calendar, Leave Requests, and Profile
- ✓ Font Awesome icons are used
- ✓ Alpine.js event bindings
- ✓ Bootstrap utility classes for styling
- ✓ Responsive breakpoints (lg, md, xs)

## Configuration

The test configuration is defined in `phpunit.xml`:
- Bootstrap file: `tests/bootstrap.php`
- Test suites organized by component
- Code coverage reporting enabled
- Strict mode enabled for test quality

## Future Enhancements

Consider adding:
- JavaScript/Alpine.js unit tests using Jest or Vitest
- Integration tests for user interactions
- Functional tests for the QR scan feature
- E2E tests using Playwright or Cypress
- Accessibility (a11y) tests
- Performance tests
