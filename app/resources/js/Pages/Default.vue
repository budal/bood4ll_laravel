<script setup lang="ts">
import ModalLayout from "@/Layouts/ModalLayout.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Form from "@/Components/Form/Index.vue";

withDefaults(
    defineProps<{
        isModal?: boolean;
        title?: string;
        form: any;
        routes?: any;
        data?: any;
        tabs?: boolean;
    }>(),
    {
        isModal: false,
        routes: [],
        tabs: true,
    },
);
</script>

<template>
    <ModalLayout v-if="isModal" :title="title">
        <Form :form="form" :routes="routes" :data="data" :tabs="tabs" />
    </ModalLayout>
    <AuthenticatedLayout v-else>
        <Form :form="form" :routes="routes" :data="data" :tabs="tabs" />
    </AuthenticatedLayout>
</template>
