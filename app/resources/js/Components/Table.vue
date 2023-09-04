<script setup lang="ts">
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid'

defineProps<{
    items: object;
}>();
</script>

<template>
  <ul role="list" class="mt-2 divide-y divide-gray-100 dark:divide-gray-600">
    <li v-for="item in items" :key="item.email" class="flex justify-between gap-x-6 py-5">
      <div class="flex min-w-0 gap-x-4">
        <img class="h-12 w-12 flex-none rounded-full bg-gray-50 dark:bg-gray-600" :src="item.imageUrl" alt="" />
        <div class="min-w-0 flex-auto">
          <p class="text-sm font-semibold leading-6 text-gray-900 dark:text-gray-100">{{ item.name }}</p>
          <p class="mt-1 truncate text-xs leading-5 text-gray-600 dark:text-gray-400">{{ item.email }}</p>
        </div>
      </div>
      <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
        <p class="text-sm leading-6 text-gray-900 dark:text-gray-100">{{ item.role }}</p>
        <p v-if="item.lastSeen" class="mt-1 text-xs leading-5 text-gray-600 dark:text-gray-400">
          Last seen <time :datetime="item.lastSeenDateTime">{{ item.lastSeen }}</time>
        </p>
        <div v-else class="mt-1 flex items-center gap-x-1.5">
          <div class="flex-none rounded-full bg-emerald-500/20 p-1">
            <div class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
          </div>
          <p class="text-xs leading-5 text-gray-600 dark:text-gray-400">Online</p>
        </div>
      </div>
    </li>
  </ul>
  <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6">
    <div class="flex flex-1 justify-between sm:hidden">
      <a href="#" class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-700 bg-gray-800 dark:bg-gray-200 px-4 py-2 text-sm uppercase font-medium text-white dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">Previous</a>
      <a href="#" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-700 bg-gray-800 dark:bg-gray-200 px-4 py-2 text-sm uppercase font-medium text-white dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">Next</a>
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-800 dark:text-white">
          Showing
          {{ ' ' }}
          <span class="font-medium">1</span>
          {{ ' ' }}
          to
          {{ ' ' }}
          <span class="font-medium">10</span>
          {{ ' ' }}
          of
          {{ ' ' }}
          <span class="font-medium">97</span>
          {{ ' ' }}
          results
        </p>
      </div>
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <a href="#" class="relative inline-flex items-center rounded-l-md px-2 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
            <span class="sr-only">Previous</span>
            <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
          </a>
          <a href="#" aria-current="page" class="relative z-10 inline-flex items-center bg-indigo-600 px-4 py-2 text-sm font-semibold text-white dark:text-gray-800 focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">1</a>
          <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">2</a>
          <a href="#" class="relative hidden items-center px-4 py-2 text-sm font-semibold bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0 md:inline-flex">3</a>
          <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 focus:outline-offset-0">...</span>
          <a href="#" class="relative hidden items-center px-4 py-2 text-sm font-semibold bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0 md:inline-flex">8</a>
          <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">9</a>
          <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">10</a>
          <a href="#" class="relative inline-flex items-center rounded-r-md px-2 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
            <span class="sr-only">Next</span>
            <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
          </a>
        </nav>
      </div>
    </div>
  </div>
</template>