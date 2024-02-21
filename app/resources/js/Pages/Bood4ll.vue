<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import ScrollTop from "primevue/scrolltop";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import ColumnGroup from "primevue/columngroup";
import Row from "primevue/row";
import Button from "primevue/button";

import NavBar from "@/Components/NavBar.vue";
import TailwindIndicator from "@/Components/TailwindIndicator.vue";
// @ts-expect-error
import { Modal } from "/vendor/emargareten/inertia-modal";

withDefaults(
    defineProps<{
        isGuest?: boolean;
        isModal?: boolean;
        title?: string;
        form?: any;
        routes?: any;
        data?: any;
        tabs?: boolean;
        status?: string;
        statusTheme?: string | null;
    }>(),
    {
        isGuest: false,
        isModal: false,
        routes: [],
        tabs: true,
    },
);
</script>

<template>
    <div class="relative min-h-screen bg-zero-white dark:bg-zero-black">
        <nav
            class="bg-zero-light dark:bg-zero-dark sm:sticky sm:top-0 z-[10] border-b"
        >
            <div
                v-if="$page.props.auth.previousUser === true"
                class="font-medium bg-danger-light dark:bg-danger-dark text-xs text-center text-danger-light dark:text-danger-dark"
            >
                {{
                    $t(
                        "You are managing information as a different account than you are logged in to. Be cautious.",
                    )
                }}
                <Link
                    :href="route('apps.users.return_to_my_user')"
                    as="button"
                    method="post"
                    >[ {{ $t("Log out") }} ]
                </Link>
            </div>
            <div class="max-w-7xl mx-auto py-1 px-2 sm:px-6 lg:px-8">
                <NavBar />
            </div>
        </nav>
        <div class="pt-8">
            <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 space-y-6">
                <div class="border rounded-lg text-sm">
                    <template v-for="content in data">
                        <TabView>
                            <TabPanel
                                :key="content.id"
                                :header="$t(content.label)"
                            >
                                <span class="m-0 text-xs">
                                    {{ $t(content.description) }}
                                </span>
                                <template v-for="items in content.items">
                                    <template v-for="item in items">
                                        <DataTable
                                            v-if="item.type == 'table'"
                                            :value="item.content.items.data"
                                            class="text-sm"
                                        >
                                            <template #header>
                                                <div
                                                    class="flex flex-wrap align-items-center justify-content-between gap-2"
                                                >
                                                    <Button
                                                        icon="pi pi-refresh"
                                                        rounded
                                                        raised
                                                    />
                                                </div>
                                            </template>
                                            <Column
                                                v-for="column in item.content
                                                    .titles"
                                                :field="column.field"
                                                :header="$t(column.title)"
                                            >
                                                <template #body="slotProps">
                                                    {{ console.log(slotProps) }}

                                                    {{
                                                        slotProps.data[
                                                            column.field
                                                        ]
                                                    }}
                                                </template>
                                            </Column>
                                        </DataTable>
                                    </template>
                                </template>
                            </TabPanel>
                        </TabView>
                    </template>
                </div>

                <!-- <Form
                    :form="form"
                    :routes="routes"
                    :data="data"
                    :tabs="tabs"
                    :status="status"
                    :statusTheme="statusTheme"
                /> -->
            </div>
        </div>
    </div>
    <Modal />
    <TailwindIndicator />
    <ScrollTop />
</template>
