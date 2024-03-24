<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import Form from "@/Components/Form/Index.vue";
import { Head, usePage } from "@inertiajs/vue3";

withDefaults(
    defineProps<{
        isGuest?: boolean;
        isModal?: boolean;
        title?: string;
        form: any;
        routes?: any;
        data?: any;
        tabs?: boolean;
        status?: string;
        statusTheme?: string | null;
    }>(),
    {
        isGuest: false,
        isModal: false,
        routes: [],
        tabs: true,
    },
);
</script>

<template>
    <GuestLayout v-if="isGuest === true" :title="title">
        <Head :title="$t(title || (usePage().props.appName as string))" />
        <Form
            :form="form"
            :routes="routes"
            :data="data"
            :tabs="tabs"
            :status="status"
            :statusTheme="statusTheme"
        />
    </GuestLayout>
    <AuthenticatedLayout v-else>
        <Form
            :form="form"
            :routes="routes"
            :data="data"
            :tabs="tabs"
            :status="status"
            :statusTheme="statusTheme"
        />
    </AuthenticatedLayout>
</template>
