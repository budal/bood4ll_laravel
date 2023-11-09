<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Table from '@/Components/Table.vue';

defineProps<{
    title: string;
    subtitle: string;
    softDelete: boolean;
    routes?: object;
    status?: string;
    filters: object;
    titles: object;
    menu?: object;
    items: object;
}>();
</script>

<template>
    <section>
        <AuthenticatedLayout>
            <header>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $t(title) }}</h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $t(subtitle) }}
                </p>
            </header>
            <Table :menu="menu" :softDelete="softDelete" :routes="routes" :filters="filters" :items="items" :titles="titles" />
        </AuthenticatedLayout>
    </section>
</template>
