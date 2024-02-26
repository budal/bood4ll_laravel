<script setup lang="ts">
import { ref, Ref, inject } from "vue";
import type { DynamicDialogInstance } from "primevue/dynamicdialogoptions";
import Form from "@/Components/Form.vue";

const dialogRef = inject<Ref<DynamicDialogInstance>>("dialogRef");

const form = ref(dialogRef?.value.data);
</script>

<template>
    <Form :component="form" />
</template>
