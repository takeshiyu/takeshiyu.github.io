import { defineConfig } from 'vite'

export default defineConfig({
    root: 'docs/.kaze/dist',
    server: {
        port: 3000,
        open: true,
        watch: {
            include: ['docs/**/*.md']
        }
    },
    build: {
        rollupOptions: {
            input: []
        }
    },
    optimizeDeps: {
        include: []
    }
})