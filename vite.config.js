import { defineConfig } from 'vite';

export default defineConfig({
  base: './',
  build: {
    outDir: 'assets',
    rollupOptions: {
      input: {
        app: './src/css/app.css',
        main: './src/js/app.js'
      },
      output: {
        entryFileNames: '[name].js',
        assetFileNames: '[name].[ext]'
      }
    }
  }
});