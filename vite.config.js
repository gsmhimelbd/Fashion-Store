import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'node:path';

const production = process.env.NODE_ENV === 'production';

export default defineConfig({
  base: production ? '/build/' : '/',
  publicDir: false,
  plugins: [tailwindcss()],
  server: {
    host: '0.0.0.0',
    allowedHosts: true,
  },
  preview: {
    host: '0.0.0.0',
    allowedHosts: true,
  },
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: 'manifest.json',
    rollupOptions: {
      input: {
        home: resolve(import.meta.dirname, 'index.html'),
        profile: resolve(import.meta.dirname, 'profile.html'),
        dashboard: resolve(import.meta.dirname, 'dashboard.html'),
        admin: resolve(import.meta.dirname, 'admin.html'),
        app: resolve(import.meta.dirname, 'resources/js/app.js'),
        profileApp: resolve(import.meta.dirname, 'resources/js/profile.js'),
        dashboardApp: resolve(import.meta.dirname, 'resources/js/dashboard.js'),
        styles: resolve(import.meta.dirname, 'resources/css/app.css'),
      },
    },
  },
});
