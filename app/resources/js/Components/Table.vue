<script setup lang="ts">
import { ref, watch, onBeforeUnmount } from 'vue'
import { router, Link } from '@inertiajs/vue3';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid'
import debounce from "lodash.debounce";
import SearchInput from '@/Components/SearchInput.vue';

const props = defineProps<{
    filters: any;
    items: any;
}>();

const value = ref("");

const debouncedWatch = debounce(() => {
    router.visit('http://localhost/apps/users?search='+value.value, {
      method: 'get',
      preserveState: true,
    })
}, 300);

watch(value, debouncedWatch);

onBeforeUnmount(() => {
  debouncedWatch.cancel();
})

</script>

<template>
  <SearchInput :placeholder="$t('Search...')" class="mt-3 w-96" :value="filters.search" v-model="value" />
  <div>
    <ul role="list" class="mt-2 divide-y divide-gray-100 dark:divide-gray-600">
      <template v-for="(item, key) in items.data">
        <li
          v-if="items.data[key].name[0] != items.data[key-1]?.name[0]"
          class="sticky top-[60px] px-4 py-3 flex items-center font-semibold text-sm text-slate-900 dark:text-slate-200 bg-slate-50/90 dark:bg-slate-700/90 backdrop-blur-sm ring-1 ring-slate-900/10 dark:ring-black/10">
          {{ items.data[key].name[0] }}
        </li>
        <li class="flex justify-between gap-x-6 px-3 py-3 hover:bg-gray-50 dark:hover:bg-gray-700">
          <div class="flex min-w-0 gap-x-4">
            <img class="h-12 w-12 flex-none rounded-full bg-gray-50 dark:bg-gray-600" :src="item.imageUrl" alt="" />
            <div class="min-w-0 flex-auto">
              <p class="text-sm font-semibold leading-6 text-gray-900 dark:text-gray-100">{{ item.name }}</p>
              <p class="mt-1 truncate text-xs leading-5 text-gray-600 dark:text-gray-400">{{ item.username }} / {{ item.email }}</p>
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
      </template>
      <li v-if="items.data.length === 0" class="flex justify-between gap-x-6 px-3 py-3">
        <p class="text-sm leading-6 text-gray-400 dark:text-gray-600">{{ $t('No items to show.') }}</p>
      </li>
    </ul>
    <div v-if="items.last_page > 1" class="flex items-center justify-between bg-white dark:bg-gray-800 px-4 py-3 sm:px-6">
      <div class="w-full flex justify-between sm:hidden">
        <div>
          <Link v-if="items.prev_page_url" :href="items.prev_page_url" class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-700 bg-gray-800 dark:bg-gray-200 px-4 py-2 text-sm uppercase font-medium text-white dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">{{ $t('Previous')}}</Link>
        </div>
        <div>
          <Link v-if="items.next_page_url" :href="items.next_page_url" class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-700 bg-gray-800 dark:bg-gray-200 px-4 py-2 text-sm uppercase font-medium text-white dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">{{ $t('Next')}}</Link>
        </div>
      </div>
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div class="">
          <p class="hidden lg:block text-xs text-gray-800 dark:text-white">
            {{ $t('Showing :from to :to of :total results', { from: items.from, to: items.to, total: items.total }) }}
          </p>
        </div>
        <div>
          <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
            <span 
                v-if="!items.prev_page_url"
                aria-current="page" class="relative inline-flex items-center rounded-l-md px-1 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
            </span>
            <Link v-if="items.prev_page_url" :href="items.prev_page_url" class="relative inline-flex items-center rounded-l-md px-2 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
              <span class="sr-only">{{ $t('Previous')}}</span>
              <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
            </Link>
            <span v-for="item in items.links">
              <Link
                v-if="item.label > 0 && item.label != items.current_page"
                :key="item.key" :href="item.url" :active="item.active" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
                {{ item.label }}
              </Link>
              <span 
                v-if="item.label == items.current_page"
                aria-current="page" class="relative z-10 inline-flex items-center bg-gray-400 px-4 py-2 text-sm font-semibold text-white dark:text-gray-800 focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">
                {{ item.label }}
              </span>
            </span>
            <Link v-if="items.next_page_url" :href="items.next_page_url" class="relative inline-flex items-center rounded-r-md px-2 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
              <span class="sr-only">{{ $t('Next')}}</span>
              <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
            </Link>
            <span 
                v-if="!items.next_page_url"
                aria-current="page" class="relative inline-flex items-center rounded-r-md px-1 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
            </span>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>