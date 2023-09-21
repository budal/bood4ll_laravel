<template>
    <button
        class="hover:scale-105 group/edit inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 disabled:opacity-50 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-500"
    >
        <div class="group-hover/edit:scale-110 transition ease-in-out duration-300">
            <slot />
        </div>
    </button>
</template>
