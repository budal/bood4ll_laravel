<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title: string;
    href: string;
    description: string;
}>();
</script>

<template>
    <Link :href=href>
        <div class="h-full bg-white hover:bg-gray-200 dark:bg-gray-900 hover:dark:bg-gray-700 rounded-lg px-6 py-8 ring-1 ring-gray-900/5 shadow-xl">
            <h3 class="text-gray-900 dark:text-white text-base font-medium tracking-tight">{{ $t(title) }}</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">{{ $t(description) }}</p>
        </div>
    </Link>
</template>
