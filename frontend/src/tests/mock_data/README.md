# Mock Data Folder (`src/test/mock_data/`)

This folder contains static mock data (dummy data) used across our **Vitest** testing suites. 

Instead of making real network calls to a backend or database during testing, we import these files to instantly simulate realistic data structures.

---

## Why Use Mock Data?

1. **Speed:** Reading local files is instantaneous, making our test suites run significantly faster.
2. **Consistency:** Real database data can change or get deleted. Mock data stays exactly the same, ensuring our tests don't randomly fail.
3. **Offline Capability:** Tests can be run entirely locally without requiring an internet connection or an active backend server.

---

## Directory Structure

Keep data organized by the features or pages they belong to:

```text
src/test/mock_data/
├── authMockData.js       # Mock credentials, user profiles, tokens
├── workersMockData.js    # Dummy datasets for worker listings
├── attendanceMock.js     # Mock QR scans, check-in logs, and time records
└── README.md             # This documentation file
```

---

## Reference Example

Here is a typical example of how a mock data file is structured and how it is imported into a test file.

1. **Defining the Mock Data (`authMockData.js`)**

// Export clean, explicit objects that mimic our database/API responses

export const mockAdminUser = {
  id: "USR-001",
  email: "admin@clockit.app",
  role: "administrator",
  name: "Gazelle Pearson",
  organization: "LCS"
};

export const mockLoginErrorResponse = {
  status: 401,
  message: "Incorrect email or password. Please check your Clock It credentials."
};

2. **Using it inside a Test File (Login.test.tsx)**

import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import App from '../App'

// Import your mock data from this directory
import { mockAdminUser } from './mock_data/authMockData'

describe('Login Profile Testing', () => {
  it('displays the user profile correctly when injected', () => {
    // You can use the mock object to verify component behavior or fill state
    console.log(`Testing with user: ${mockAdminUser.name}`);
    
    // Test logic goes here...
  })
})