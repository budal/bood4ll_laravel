<script setup lang="ts">
import { ref, Ref, inject } from "vue";
import type { DynamicDialogInstance } from "primevue/dynamicdialogoptions";
import Structure from "@/Components/Structure.vue";

const dialogRef = inject<Ref<DynamicDialogInstance>>("dialogRef");

const data = ref(dialogRef?.value.data);
</script>

<template>
    <Structure
        :structure="data.action.component"
        :tabs="data.action.tabs"
        :id="data.id"
    />
</template>
