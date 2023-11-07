<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Form from '@/Components/Form.vue';

defineProps<{
    form: any;
    routes: any;
    data: any;
}>();

</script>

<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <Form :form="form" :routes="routes" :data="data" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
