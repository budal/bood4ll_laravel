<script setup lang="ts">
import { ref, Ref, inject } from "vue";
import type { DynamicDialogInstance } from "primevue/dynamicdialogoptions";
import Structure from "@/Components/Structure.vue";

const dialogRef = inject<Ref<DynamicDialogInstance>>("dialogRef");

const data = ref(dialogRef?.value.data);
</script>

<template>
    <Structure
        :build="data.action.components"
        :tabs="data.action.tabs"
        :id="data.id"
        :dialogRef="dialogRef"
    />
</template>
