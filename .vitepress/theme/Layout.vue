<script setup lang="ts">
import { useData } from 'vitepress'

import Home from './Home.vue'
import Article from './Article.vue'
import Footer from './Footer.vue'
import NotFound from './NotFound.vue'

const { page, frontmatter } = useData()
</script>

<template>
  <div class="flex flex-col min-h-screen justify-between">
    <div class="absolute inset-0 -z-10 mx-0 max-w-none overflow-hidden flex-grow">
      <div
        class="absolute left-1/2 top-0 ml-[-38rem] h-[35rem] w-[81.25rem] [mask-image:linear-gradient(white,transparent)]">
        <div
          class="absolute inset-0 bg-gradient-to-r [mask-image:radial-gradient(farthest-side_at_top,white,transparent)] from-[#36b49f]/30 to-[#DBFF75]/30 opacity-100">
          <div
            class="my-pattern inset-x-0 inset-y-[-50%] h-[200%] w-full skew-y-[-0deg] mix-blend-overlay fill-white/2.5 stroke-white/5">
          </div>
        </div>
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
