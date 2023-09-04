<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Table from '@/Components/Table.vue';

import { Head } from '@inertiajs/vue3';

defineProps<{
    items: object;
}>();
</script>

<template>
  <ul role="list" class="divide-y divide-gray-100 dark:divide-slate-600">
    <li v-for="item in items" :key="item.email" class="flex justify-between gap-x-6 py-5">
      <div class="flex min-w-0 gap-x-4">
        <img class="h-12 w-12 flex-none rounded-full bg-gray-50" :src="item.imageUrl" alt="" />
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
</template>