# Pages Folder (`src/pages/`)

This folder contains the root-level components that represent the distinct views or screens of the application. 

Unlike the generic `components/` folder, files here are directly tied to application routes and act as the orchestrators for data fetching, state injection, and page layout.

---

## What Goes Here?

* **Route Targets:** Any component that serves as a main destination for a URL route (e.g., `/login`, `/dashboard`).
* **Container Components:** Top-level wrappers that fetch data from APIs/services and pass that data down to presentation components.
* **Page Layout Assemblies:** Files that assemble smaller, reusable components from `src/components/` into a complete page structure.

## Structure Example

To align with the project objectives of a real-time attendance system, the pages are structured around major feature flows:

```text
src/pages/
├── Login/
│   ├── LoginPage.jsx        # The main /login view
│   └── LoginPage.test.jsx   # Page-level integration tests
├── Dashboard/
│   ├── DashboardPage.jsx    # Live dashboard view showing onsite staff
│   └── OnsiteTable.jsx      # Sub-component specific only to this page
└── Profile/
    └── ProfilePage.jsx      # Staff configuration & settings view
```