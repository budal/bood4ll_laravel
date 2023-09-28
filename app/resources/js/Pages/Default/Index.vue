<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Table from '@/Components/Table.vue';

defineProps<{
    title: string;
    subtitle: string;
    routes?: object;
    status?: string;
    filters: object;
    items: object;
    titles: object;
}>();

</script>

<template>
    <section>
        <AuthenticatedLayout>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $t(title) }}</h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ $t(subtitle) }}
                            </p>
                        </header>
                        <Table :routes="routes" :filters="filters" :items="items" :titles="titles" />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </section>
</template>
