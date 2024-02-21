<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import ScrollTop from "primevue/scrolltop";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import TreeTable from "primevue/treetable";
import Column from "primevue/column";

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
        {{ console.log(data) }}
        <div class="pt-8">
            <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 space-y-6">
                <div class="border rounded-lg text-sm">
                    <TabView>
                        <TabPanel
                            v-for="item in data"
                            :key="item.id"
                            :header="$t(item.label)"
                        >
                            <span class="m-0 text-xs">
                                {{ $t(item.description) }}
                            </span>

                            <template v-for="subitem in item.fields">
                                <template v-for="element in subitem">
                                    <TreeTable
                                        v-if="element.type == 'table'"
                                        :value="nodes"
                                        class="text-sm"
                                    >
                                        <Column
                                            field="name"
                                            header="Name"
                                            expander
                                        ></Column>
                                        <Column
                                            field="size"
                                            header="Size"
                                        ></Column>
                                        <Column
                                            field="type"
                                            header="Type"
                                        ></Column>
                                    </TreeTable>
                                </template>
                            </template>
                        </TabPanel>
                    </TabView>
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
