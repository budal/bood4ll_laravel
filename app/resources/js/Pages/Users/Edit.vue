<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UsersList from './Partials/UsersList.vue';

import { Head } from '@inertiajs/vue3';

defineProps<{
    data: any;
}>();

</script>

<template>
    {{ console.log(data) }}
    <Head :title="$t('User edit')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $t('User edit') }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
