<script setup lang="ts">
import { data as posts } from './posts.data.js'
import Header from './Header.vue'
</script>

<template>
    <main class="relative max-w-[52rem] py-16 mx-auto px-4 pb-28 sm:px-6 md:px-8 xl:px-12 lg:max-w-6xl">
        <Header />
        <div
            class="relative sm:pb-12 sm:ml-[calc(2rem+1px)] md:ml-[calc(3.5rem+1px)] lg:ml-[max(calc(14.5rem+1px),calc(100%-48rem))]">
            <div
                class="hidden absolute top-3 bottom-0 right-full mr-7 md:mr-[3.25rem] w-px bg-slate-200 dark:bg-slate-800 sm:block">
            </div>
            <div class="space-y-16">
                <article class="relative group" v-for="{ title, url, date, excerpt } of posts">
                    <div
                        class="absolute -inset-y-2.5 -inset-x-4 md:-inset-y-4 md:-inset-x-6 sm:rounded-2xl group-hover:bg-zinc-50/70 dark:group-hover:bg-zinc-800/50">
                    </div><svg viewBox="0 0 9 9"
                        class="hidden absolute right-full mr-6 top-2 text-zinc-200 dark:text-zinc-600 md:mr-12 w-[calc(0.5rem+1px)] h-[calc(0.5rem+1px)] overflow-visible sm:block">
                        <circle cx="4.5" cy="4.5" r="4.5" stroke="currentColor" class="fill-white dark:fill-zinc-900"
                            stroke-width="2"></circle>
                    </svg>
                    <div class="relative">
                        <h3
                            class="text-base font-semibold tracking-tight text-zinc-900 dark:text-zinc-200 pt-8 lg:pt-0">
                            {{ title }}</h3>
                        <div class="mt-2 mb-4 prose prose-zinc prose-a:relative prose-a:z-10 dark:prose-dark line-clamp-2"
                            v-html="excerpt">
                        </div>
                        <dl class="absolute left-0 top-0 lg:left-auto lg:right-full lg:mr-[calc(6.5rem+1px)]">
                            <dt class="sr-only">Date</dt>
                            <dd class="whitespace-nowrap text-sm leading-6 dark:text-zinc-400"><time
                                    :datetime="date.string">{{ date.string }}</time></dd>
                        </dl>
                    </div>
                    <a class="flex items-center text-sm text-emerald-500 font-medium" :href="url"><span
                            class="absolute -inset-y-2.5 -inset-x-4 md:-inset-y-4 md:-inset-x-6 sm:rounded-2xl"></span><span
                            class="relative">Read more<span class="sr-only">, {{ title }}</span></span><svg
                            class="relative mt-px overflow-visible ml-2.5 text-emerald-700" width="3" height="6"
                            viewBox="0 0 3 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M0 0L3 3L0 6"></path>
                        </svg></a>
                </article>
            </div>
        </div>
    </main>

</template>