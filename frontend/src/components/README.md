# Components Folder (`src/components/`)

This folder contains the reusable, modular UI components used throughout the application. 

As a general rule, files in this folder should be **presentational or structural building blocks** that can be reused across multiple pages or views.

---

## What Goes Here?

*   **Global/Shared UI Elements:** Inputs, buttons, modals, cards, tooltips, and loaders.
*   **Layout Components:** Sidebars, navbars, footers, and grid wrappers.
*   **Pure Components:** Components that rely primarily on props for data and behavior, making them highly predictable and reusable.

## Structure Example

We follow a **component folder pattern** where each major component gets its own folder containing the component file, styles, and tests:

```text
src/components/
├── common/                  # Atomic, highly reusable UI elements
│   ├── Button/
│   │   ├── Button.jsx
│   │   ├── Button.module.css
│   │   └── Button.test.jsx
│   └── Input/
│       ├── Input.jsx
│       └── Input.module.css
└── layout/                  # Structural components
    ├── Navbar/
    │   └── Navbar.jsx
    └── Sidebar/
        └── Sidebar.jsx
```

----

## Best Practices

* Keep it Focused: A component should do one thing well. If a component grows too large, break it down into smaller sub-components.

* Separate Pages from Components: Do not put entire page views here. Route-level pages belong in a dedicated src/pages/ or src/views/ directory.

* Use CSS Modules / Scoped Styles: Keep component styles isolated (e.g., ComponentName.module.css) to prevent global style bleeding.

* Props Documentation: Always destructure props clearly or use PropTypes / TypeScript interfaces to document the component's expected inputs.

``` Component Example ```

import React from 'react';
import styles from './Button.module.css';

function Button({ label, onClick, type = 'button', variant = 'primary' }) {
  return (
    <button 
      type={type} 
      className={`${styles.btn} ${styles[variant]}`} 
      onClick={onClick}
    >
      {label}
    </button>
  );
}

export default Button;

