<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { useDark } from "@vueuse/core"

const isDark = useDark();
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-zero-light dark:bg-zero-dark">
        <div>
            <Link href="/">
                <ApplicationLogo class="w-20 h-20 fill-current text-zero-light dark:text-zero-dark" />
            </Link>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-secondary-light dark:bg-secondary-dark shadow-md overflow-hidden sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
