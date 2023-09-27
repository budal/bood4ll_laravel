<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormInfo from './Partials/FormInfo.vue';

defineProps<{
    body: any;
}>();

</script>

<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <FormInfo :body="body"/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
