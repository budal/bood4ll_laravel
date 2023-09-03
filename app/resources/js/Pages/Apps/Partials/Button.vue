<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title: String;
    href: string;
    description?: String;
}>();
</script>

<template>
    <Link :href=href>
        <div
            class="px-4 py-5 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
            <h2 class="font-semibold text-xl text-white dark:text-gray-800 leading-tight">{{ title }}</h2>
            <p class="mt-1 text-sm text-gray-200 dark:text-gray-500">{{ description }}</p>
        </div>
    </Link>

</template>
