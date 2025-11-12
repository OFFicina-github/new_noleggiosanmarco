import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  build: {
    outDir: 'vue-app/dist',     // Dove salvare i file compilati
    emptyOutDir: true,
    rollupOptions: {
      input: resolve(__dirname, 'vue-app/main.js'), // Entry corretto!
      output: {
        entryFileNames: 'bundle.js',
        assetFileNames: 'bundle.[ext]',
      },
    },
  },
})
