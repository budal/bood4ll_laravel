<script setup lang="ts">
import { computed } from 'vue';
import { Link, Head, usePage } from '@inertiajs/vue3';

const insertBetween = (items, insertion) => {
    return items.flatMap(
        (value, index, array) =>
            array.length - 1 !== index
                ? [value, insertion]
                : value,
    )
}

const breadcrumbs = computed(() => insertBetween(usePage().props.breadcrumbs || [], '/'))
</script>

<template>
    <Head :title="$t('Users')" />

    <header v-if="breadcrumbs.length > 0" class="bg-white dark:bg-gray-800 shadow">
        <div class="flex gap-4 max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 v-for="page in breadcrumbs" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                <span v-if="page ==='/'">/</span>
                <Link
                    v-else
                    :href="page.url"
                    :disabled="page.current"
                >{{ $t(page.title) }}</Link>
            </h2>
        </div>
    </header>
</template>
