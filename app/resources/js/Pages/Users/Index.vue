<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UsersList from './Partials/UsersList.vue';

import { Head } from '@inertiajs/vue3';

defineProps<{
    filters: any;
    items: any;
}>();

</script>

<template>
    <Head :title="$t('Users')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $t('Users') }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <UsersList 
                        :filters="filters" 
                        :items="items" 
                        indexRoute="apps.users" 
                        createRoute="apps.users.create" 
                        editRoute="apps.users.edit" 
                        destroyRoute="apps.users.destroy" 
                        restoreRoute="apps.users.restore" 
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
