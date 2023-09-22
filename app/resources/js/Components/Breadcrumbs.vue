<script setup lang="ts">
import { computed } from 'vue';
import { Link, Head, usePage } from '@inertiajs/vue3';

const insertBetween = (items: any, insertion: string) => {
    return items.flatMap(
        (value: any, index: number, array: string | any[]) =>
            array.length - 1 !== index
                ? [value, insertion]
                : value,
    )
}

const breadcrumbs = computed(() => insertBetween(usePage().props.breadcrumbs || [], '/'))
</script>

<template>
    <Head :title="$t(breadcrumbs[breadcrumbs.length -1]?.title || usePage().props.appName)" />

    <header v-if="breadcrumbs.length > 0" class="bg-white dark:bg-gray-800 shadow">
        <div class="flex gap-4 max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 v-for="page in breadcrumbs" class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                <span v-if="page ==='/'" class="text-gray-400 dark:text-gray-500">/</span>
                <template v-else>
                    <Link v-if="page.current !== true"
                        :href="page.url"
                        :disabled="page.current"
                        as="button"
                    >
                        {{ $t(page.title) }}
                    </Link>
                    <span v-else>
                        {{ $t(page.title) }}
                    </span>
                </template>
            </h2>
        </div>
    </header>
</template>
