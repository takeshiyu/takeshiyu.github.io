import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Takeshi Yu",
  description: "Just simple folk, with HTML, trying to make a living",
  cleanUrls: true,
  markdown: {
    theme: 'slack-dark'
  },
  head: process.env.NODE_ENV === 'production'
    ? [
      [
        'script',
        {
          src: 'https://cdn.usefathom.com/script.js', 'data-site': 'HYVQUQMB',
          defer: true
        }
      ]
    ] : []
})
