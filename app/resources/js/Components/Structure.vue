<script setup lang="ts">
import Form from "@/Components/Form.vue";
import { DynamicDialogInstance } from "primevue/dynamicdialogoptions";

withDefaults(
    defineProps<{
        build?: any;
        tabs?: boolean;
        id?: string | number;
        dialogRef?: DynamicDialogInstance;
    }>(),
    {
        tabs: true,
    },
);
</script>

<template>
    <Message
        v-if="$page.props.status"
        :severity="($page.props.statusTheme as string) || 'info'"
    >
        {{ $t($page.props.status as string) }}
    </Message>

    <TabView v-if="build.length > 1 && tabs == true">
        <template
            v-for="item in build.filter((item: any) => item?.visible != false)"
        >
            <TabPanel
                :header="$t(item.label)"
                :pt="{ content: { class: '-mx-4' } }"
            >
                <Form :components="item" :id="id" :dialogRef="dialogRef" />
            </TabPanel>
        </template>
    </TabView>
    <div v-else class="grid gap-4 grid-cols-1">
        <template
            v-for="item in build.filter((item: any) => item.visible != false)"
        >
            <Form :components="item" :id="id" :dialogRef="dialogRef" />
        </template>
    </div>
</template>
