<script setup lang="ts">
import { computed } from 'vue'
import { useData, useRoute } from 'vitepress'
import { data as posts } from './posts.data.js'
import Author from './Author.vue'

const { frontmatter: data } = useData()

const route = useRoute()

function findCurrentIndex() {
    return posts.findIndex((p) => p.url === route.path)
}

// use the customData date which contains pre-resolved date info
const date = computed(() => posts[findCurrentIndex()].date)
const nextPost = computed(() => posts[findCurrentIndex() - 1])
const prevPost = computed(() => posts[findCurrentIndex() + 1])
</script>


<template>
    <!-- <Nav /> -->

    <div class="overflow-hidden">
        <div class="max-w-8xl mx-auto">
            <div class="flex px-4 pt-8 pb-10 lg:px-8"><a
                    class="group flex font-semibold text-sm leading-6 text-slate-700 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white"
                    href="/"><svg viewBox="0 -9 3 24"
                        class="overflow-visible mr-3 text-slate-400 w-auto h-6 group-hover:text-slate-600 dark:group-hover:text-slate-300">
                        <path d="M3 0L0 3L3 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"></path>
                    </svg>Go back</a></div>
        </div>
        <div class="px-4 sm:px-6 md:px-8">
            <div class="max-w-3xl mx-auto">
                <main>
                    <article class="relative pt-10">
                        <h1
                            class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-200 md:text-3xl ">
                            {{ data.title }}</h1>
                        <div class="text-sm leading-6">
                            <dl>
                                <dt class="sr-only">Date</dt>
                                <dd class="absolute top-0 inset-x-0 text-slate-700 dark:text-slate-400"><time
                                        datetime="2024-11-21T18:30:00.000Z">{{ date.string }}</time></dd>
                            </dl>
                        </div>
                        <Author />
                        <div class="mt-12 prose prose-slate dark:prose-dark">
                            <Content />
                        </div>
                    </article>
                </main>
            </div>
        </div>
    </div>
</template>