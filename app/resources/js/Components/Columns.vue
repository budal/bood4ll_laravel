<script setup lang="ts">
import Form from "@/Components/Form.vue";
import { DynamicDialogInstance } from "primevue/dynamicdialogoptions";
import Structure from "@/Components/Structure.vue";

withDefaults(
    defineProps<{
        structure?: any;
        tabs?: boolean;
        id?: string | number;
        formValue?: any;
        dialogRef?: DynamicDialogInstance;
    }>(),
    {
        tabs: true,
    },
);
</script>

<template>
    <div class="flex flex-column md:flex-row">
        <div
            v-for="(build, key) of structure"
            class="w-full flex align-items-center justify-center"
        >
            <!-- <Structure :build="build" :tabs="tabs" /> -->
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
