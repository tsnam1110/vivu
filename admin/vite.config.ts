import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    // Dải 52xx để tránh xung đột với Vite mặc định (5173)
    port: 5200,
    strictPort: true,
  },
})
