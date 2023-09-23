<template>
    <button
        class="hover:scale-105 group/edit inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-800 dark:focus:ring-gray-200 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-500"
    >
        <div class="group-hover/edit:scale-110 transition ease-in-out duration-300">
            <slot />
        </div>
    </button>
</template>
