<script setup lang="ts">
    import ModalLayout from '@/Layouts/ModalLayout.vue';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import Form from '@/Components/Form.vue';

    withDefaults(
        defineProps<{
            modal?: boolean;
            form: any;
            routes: any;
            data?: any;
        }>(),
        {
            modal: false,
        }
    );
</script>

<template>
    <ModalLayout v-if="modal">
        <Form :form="form" :routes="routes" :data="data" />
    </ModalLayout>
    <AuthenticatedLayout v-else>
        <Form :form="form" :routes="routes" :data="data" />
    </AuthenticatedLayout>
</template>
