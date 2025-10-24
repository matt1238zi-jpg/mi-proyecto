import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/js/app.js',   // Inertia
        'resources/js/auth.js',  // Login/registro
        'resources/css/app.css',
      ],
      refresh: true,
    }),
    vue(),
  ],
})
