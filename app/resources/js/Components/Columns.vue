<script setup lang="ts">
import Form from "@/Components/Form.vue";
import { DynamicDialogInstance } from "primevue/dynamicdialogoptions";

defineProps<{
    structure?: any;
    id?: string | number;
    formValue?: any;
    dialogRef?: DynamicDialogInstance;
}>();
</script>

<template>
    <div class="flex flex-column md:flex-row gap-3">
        <div
            v-for="(build, key) of structure"
            class="w-full flex align-items-center justify-center"
        >
            <Form
                :components="build"
                :key="key"
                :id="id"
                :formValue="formValue"
                :dialogRef="dialogRef"
            />
        </div>
    </div>
</template>
