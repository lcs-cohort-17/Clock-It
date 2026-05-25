# Tests Folder (`src/tests/`)

Welcome to the test suite! This folder is dedicated to **Unit and Integration Testing** using **Vitest** and **React Testing Library**.
Writing tests ensures our application works exactly as expected and doesn't break when we make changes.

---

## Naming Conventions

To keep our tests organized and automatically detected by the test runner, please use the following file naming structure:

### Naming Structure
`[FeatureName].test.tsx`

*   **Example:** `Login.test.tsx` or `ClockItLogin.test.tsx`

> **Note:** Standard practice in React communities uses `.test.tsx` or `.spec.tsx` so the testing tools instantly find your files without extra configuration. But in our case we are using `.test.tsx`.

---

## 🛠️ How to Run the Tests

Open your terminal and run one of the following commands:

*   **Watch Mode (Development):** `npm run test` or `npx vitest`  
    *This keeps the terminal open and automatically re-runs tests whenever you save a file.*
*   **Single Run (CI/Production):** `npm run test:run` or `npx vitest run`  
    *This runs all tests once and finishes.*