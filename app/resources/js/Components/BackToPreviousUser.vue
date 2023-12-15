<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
</script>

<template>
    <div
        v-if="usePage().props.appName !== 'production'"
        className="fixed top-20 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-primary-light dark:bg-primary-dark p-3 font-mono text-xs text-primary-light dark:text-primary-dark"
    >
        <div className="block sm:hidden">xs</div>
        <div className="hidden sm:block md:hidden">sm</div>
        <div className="hidden md:block lg:hidden">md</div>
        <div className="hidden lg:block xl:hidden">lg</div>
        <div className="hidden xl:block 2xl:hidden">xl</div>
        <div className="hidden 2xl:block">2xl</div>
    </div>
</template>
