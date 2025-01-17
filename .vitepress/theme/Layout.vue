<script setup lang="ts">
import { useData } from 'vitepress'

import Home from './Home.vue'
import Article from './Article.vue'
import Footer from './Footer.vue'
import NotFound from './NotFound.vue'

const { page, frontmatter } = useData()
</script>

<template>
  <div class="" flex flex-col>
    <div class="absolute inset-0 -z-10 mx-0 max-w-none overflow-hidden">
      <div
        class="absolute left-1/2 top-0 ml-[-38rem] h-[35rem] w-[81.25rem] dark:[mask-image:linear-gradient(white,transparent)]">
        <div
          class="absolute inset-0 bg-gradient-to-r from-[#36b49f] to-[#DBFF75] opacity-40 [mask-image:radial-gradient(farthest-side_at_top,white,transparent)] dark:from-[#36b49f]/30 dark:to-[#DBFF75]/30 dark:opacity-100">
          <div
            class="my-pattern inset-x-0 inset-y-[-50%] h-[200%] w-full skew-y-[-0deg] fill-black/40 stroke-black/50 mix-blend-overlay dark:fill-white/2.5 dark:stroke-white/5">
          </div>
        </div>
        <svg viewBox="0 0 1113 440" aria-hidden="true"
          class="absolute left-1/2 top-0 ml-[-19rem] w-[69.5625rem] fill-white blur-[26px] dark:hidden">
          <path d="M.016 439.5s-9.5-300 434-300S882.516 20 882.516 20V0h230.004v439.5H.016Z"></path>
        </svg>
      </div>
    </div>

    <div class="flex-grow">
      <Home v-if="frontmatter.home" />
      <NotFound v-else-if="page.isNotFound" />
      <Article v-else />
    </div>

    <Footer />
  </div>
</template>
