<script setup lang="ts">
defineProps<{
    title?: String;
    description?: String;
}>();
</script>

<template>
    <button
        class="items-center px-4 py-5 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
    >
        <h2 class="font-semibold text-xl text-white dark:text-gray-800 leading-tight">{{ title }}</h2>

        <p class="mt-1 text-sm text-gray-200 dark:text-gray-500">
            Update your account's profile information and email address.
        </p>
    </button>
</template>
