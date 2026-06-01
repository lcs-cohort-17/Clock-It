import { afterEach, vi } from 'vitest'
import '@testing-library/jest-dom'
import { cleanup } from '@testing-library/react'

afterEach(() => {
  cleanup()
})

// Mock URL methods
globalThis.URL.createObjectURL = vi.fn(() => 'mock-image-url')
globalThis.URL.revokeObjectURL = vi.fn()

// Mock window.location
Object.defineProperty(window, 'location', {
  value: { href: '' },
  writable: true,
})

// Mock window.open
Object.defineProperty(window, 'open', {
  value: vi.fn(),
  writable: true,
})

// Mock indexedDB
const mockIndexedDB = {
  databases: vi.fn().mockResolvedValue([]),
  deleteDatabase: vi.fn(),
}

globalThis.indexedDB = mockIndexedDB as unknown as IDBFactory