<script setup lang="ts">
import { useData } from 'vitepress'

const { frontmatter } = useData()
</script>

<template>
    <div class="mt-6">
        <ul class="flex flex-wrap text-sm leading-6 -mt-6 -mx-5">
            <li class="flex items-center font-medium whitespace-nowrap px-5 mt-6">
                <img v-if="frontmatter.gravatar" :src="'https://gravatar.com/avatar/' + frontmatter.gravatar"
                    alt="author image" class="mr-3 w-9 h-9 rounded-full bg-slate-800" decoding="async" />

                <div class="text-sm leading-4">
                    <div class="text-slate-200">{{ frontmatter.author }}</div>
                    <div class="mt-1">
                        <a :href="'https://twitter.com/' + frontmatter.twitter" target="_blank"
                            rel="noopnener noreferrer" class=" hover:text-emerald-600 text-emerald-400">{{
                                frontmatter.twitter }}</a>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>