<script setup lang="ts">
import Modal from "@/Components/Modal.vue";

withDefaults(
    defineProps<{
        title?: string;
    }>(),
    {
        title: "",
    },
);
const { show, redirect } = useModal();
</script>

<template></template>
