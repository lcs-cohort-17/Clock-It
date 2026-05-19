# Public Folder

This directory contains static assets that are served directly to the browser without being processed by Webpack or Vite. 

Anything placed in this folder will be copied directly into the build output root.

---

## What Goes Here?

*   **Global Assets:** Files that do not change and are referenced directly via absolute URLs (e.g., `/favicon.ico`).
*   **Metadata & SEO:** Configuration files for browsers, search engines, and web apps (e.g., `manifest.json`, `robots.txt`).
*   **Third-Party Scripts:** External libraries or legacy scripts that cannot be bundled via npm/yarn.
*   **Fonts & Static Images:** Media files that are dynamically referenced or need to remain outside the JavaScript bundle.

## Structure Example

```text
public/
├── favicon.ico          # App icon for browser tabs
├── icon.png             # App icon for PWAs / social previews
├── index.html           # The main HTML page template
├── manifest.json        # Progressive Web App (PWA) metadata
└── robots.txt           # Instructions for search engine crawlers
```
---

## How to Reference Public Assets

In your React components, you can reference files in the public folder using the **public URL root path (/)**

``
<img src="/icon.png" alt="App Logo" />
``

## Note
For source code assets like components, styles, or images used inside specific components, use the **src/** directory instead so they can be optimized and bundled during production builds.