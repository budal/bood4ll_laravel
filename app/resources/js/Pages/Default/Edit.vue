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
        <Form :form="form" :routes="routes" :data="data" />
    </AuthenticatedLayout>
</template>
