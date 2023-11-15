<script setup lang="ts">
    import { useDark } from "@vueuse/core"

    const isDark = useDark();
</script>

<template>
    <div>
        <div class="relative min-h-screen bg-zero-light dark:bg-zero-dark">
            <div class="p-2">
                <div class="max-w-7xl mx-auto">
                    <div class="p-2 bg-secondary-light dark:bg-secondary-dark shadow sm:rounded-lg">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
