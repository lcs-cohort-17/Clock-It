## Assets Folder

This folder is dedicated to static assets that are **processed, optimized, and bundled** by the build tool (Webpack, Vite, etc.) as part of the application's dependency graph.

---

## What Goes Here?

* **Images & Icons:** Global graphics, illustrations, and UI icons (`.png`, `.jpg`, `.svg`, `.webp`) used inside components.
*   **Stylesheets:** Global CSS, Sass/SCSS files, or design system tokens (`.css`, `.scss`).
*   **Fonts:** Custom web fonts (`.woff`, `.woff2`, `.ttf`) required by your styles.
*   **Multimedia:** Small audio or video clips used directly within the app UI.

## Structure Example

```text
src/assets/
├── images/
│   ├── logo.svg
│   └── hero-background.jpg
├── styles/
│   ├── main.scss
│   └── variables.scss
└── fonts/
    └── CustomFont.woff2
```

---

## How To Use Assets in React

Because assets in this folder are part of the build pipline, they must be **imported** directly into your JavaScript/TypeScript files. This allows the bundler to optimize them (e.g., minifying SVGs or harshing filenames for caching)

``` Importing images ```

import React from 'react';
import Logo from '../assets/images/logo.svg';

function Header() {
  return <img src={Logo} alt="Company Logo" />;
}

export default Header;

``` Importing Styles```

// In your main index.js or App.js
import './assets/styles/main.scss';

---

## Assets vs Public

If you're not sure where to put a file, then follow this quick rule of thumb:

| Feature | `src/assets/` | `public/` |
| :--- | :--- | :--- |
| **Bundler Processed** | **Yes** (Optimized, hashed for caching) | **No** (Copied exactly as-is) |
| **How to reference** | Use `import` statements | Use absolute paths (e.g., `/filename.png`) |
| **Best used for** | Component-specific images, icons, global styles, local fonts | `favicon.ico`, `manifest.json`, `robots.txt`, third-party scripts |