<script setup lang="ts">
import Form from "@/Components/Form.vue";
import { DynamicDialogInstance } from "primevue/dynamicdialogoptions";
import { Head } from "@inertiajs/vue3";

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

    <template v-if="build.length > 1 && tabs == true">
        <TabView>
            <template
                v-for="item in build.filter(
                    (item: any) => item.visible != false,
                )"
            >
                <TabPanel
                    :header="$t(item.label)"
                    :pt="{ content: { class: '-mx-4' } }"
                >
                    <Card>
                        <template v-if="item.label" #title>
                            {{ $t(item.label) }}
                        </template>
                        <template v-if="item.description" #subtitle>
                            {{ $t(item.description) }}
                        </template>
                        <template #content>
                            <Form
                                :components="item"
                                :id="id"
                                :dialogRef="dialogRef"
                            />
                        </template>
                    </Card>
                </TabPanel>
            </template>
        </TabView>
    </template>
    <template v-else>
        <div class="grid gap-4 grid-cols-1">
            <template
                v-for="item in build.filter(
                    (item: any) => item.visible != false,
                )"
            >
                <Card>
                    <template v-if="item.label" #title>
                        {{ $t(item.label) }}
                    </template>
                    <template v-if="item.description" #subtitle>
                        {{ $t(item.description) }}
                    </template>
                    <template #content>
                        <Form
                            :components="item"
                            :id="id"
                            :dialogRef="dialogRef"
                        />
                    </template>
                </Card>
            </template>
        </div>
    </template>
</template>
