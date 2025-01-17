// https://vitepress.dev/guide/custom-theme
import './style.css'
import Layout from './Layout.vue'


/** @type {import('vitepress').Theme} */
export default {
  Layout,
  enhanceApp({ app, router, siteData }) {
    if (typeof window !== 'undefined') {
      document.documentElement.classList.add('dark')
    }
  }
}

