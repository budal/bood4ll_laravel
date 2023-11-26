<script setup lang="ts">
    import ModalLayout from '@/Layouts/ModalLayout.vue';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import Form from '@/Components/Form/Index.vue';

    withDefaults(
        defineProps<{
            isModal?: boolean;
            title?: string;
            form: any;
            routes: any;
            data?: any;
        }>(),
        {
            isModal: false,
        }
    );
</script>

<template>
    <ModalLayout v-if="isModal" :title="title">
        <Form :form="form" :routes="routes" :data="data" />
    </ModalLayout>
    <AuthenticatedLayout v-else>
        <Form :form="form" :routes="routes" :data="data" />
    </AuthenticatedLayout>
</template>
