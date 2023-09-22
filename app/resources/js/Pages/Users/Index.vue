<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UsersList from './Partials/UsersList.vue';

defineProps<{
    status?: any;
    filters: any;
    items: any;
    titles: any;
}>();

</script>

<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <UsersList 
                        :status="status" 
                        :filters="filters" 
                        :items="items" 
                        :titles="titles" 
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
