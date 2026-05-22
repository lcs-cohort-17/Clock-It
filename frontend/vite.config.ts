import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
<<<<<<< HEAD
=======
import path from 'node:path'
>>>>>>> fce12ba4e1b5c5f935464309b32a5d9d75d6c432

export default defineConfig({
  plugins: [react(), tailwindcss()],
<<<<<<< HEAD
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: './src/tests/setup.ts',
=======
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: './src/tests/setupTests.ts',
>>>>>>> fce12ba4e1b5c5f935464309b32a5d9d75d6c432
  },
})
