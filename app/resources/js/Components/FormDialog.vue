<script setup lang="ts">
import { ref, Ref, inject } from "vue";
import type { DynamicDialogInstance } from "primevue/dynamicdialogoptions";
import Build from "@/Components/Build.vue";

const dialogRef = inject<Ref<DynamicDialogInstance>>("dialogRef");

const data = ref(dialogRef?.value.data);
</script>

<template>
    <Build :component="data" />
</template>
