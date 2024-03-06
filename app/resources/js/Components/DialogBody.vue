<script setup lang="ts">
import { ref, Ref, inject } from "vue";
import type { DynamicDialogInstance } from "primevue/dynamicdialogoptions";
import Structure from "@/Components/Structure.vue";
import { isValidUrl } from "@/helpers";

const dialogRef = inject<Ref<DynamicDialogInstance>>("dialogRef");

const data = ref(dialogRef?.value.data);
</script>

<template>
    <Structure
        :structure="data.action.form.component"
        :tabs="data.action.form.tabs"
        :id="data.id"
    />
</template>
