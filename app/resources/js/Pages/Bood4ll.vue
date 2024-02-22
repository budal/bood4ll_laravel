<script setup lang="ts">
import { FilterMatchMode } from "primevue/api";
import { useToast } from "primevue/usetoast";

import Button from "primevue/button";
import Column from "primevue/column";
import DataTable, { DataTableRowReorderEvent } from "primevue/datatable";
import InputIcon from "primevue/inputicon";
import IconField from "primevue/iconfield";
import InputText from "primevue/inputtext";
import ScrollTop from "primevue/scrolltop";
import TabPanel from "primevue/tabpanel";
import TabView from "primevue/tabview";
import Toast from "primevue/toast";

import NavBar from "@/Components/NavBar.vue";
import TailwindIndicator from "@/Components/TailwindIndicator.vue";

// @ts-expect-error
import { Modal } from "/vendor/emargareten/inertia-modal";

import { ref, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import { useIntersectionObserver } from "@vueuse/core";

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

const toast = useToast();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    "country.name": { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    representative: { value: null, matchMode: FilterMatchMode.IN },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    verified: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const lastIntersection = ref(null);
const nextPageURL = ref(null);
const loadingTable = ref(true);
const contentItems = ref();
const selectedItems = ref();
const expandedRows = ref([]);

async function getData(cursor: string | null) {
    try {
        const response = await fetch(cursor ?? window.location.href + "/json");
        return await response.json();
    } catch (error) {
        console.error(error);
    }
}

const onRowReorder = (event: DataTableRowReorderEvent) => {
    contentItems.value = event.value;
    toast.add({ severity: "success", summary: "Rows Reordered", life: 3000 });
};

useIntersectionObserver(lastIntersection, ([{ isIntersecting }]) => {
    if (isIntersecting && contentItems.value != undefined) {
        if (nextPageURL.value !== null) {
            getData(nextPageURL.value).then((content) => {
                nextPageURL.value = content.next_page_url;
                contentItems.value = [...contentItems.value, ...content.data];
            });
        }
    }
});

onMounted(() => {
    getData(null).then((content) => {
        contentItems.value = content.data;
        nextPageURL.value = content.next_page_url;
        loadingTable.value = false;
    });
});
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
                                            :value="contentItems"
                                            dataKey="id"
                                            v-model:selection="selectedItems"
                                            v-model:expandedRows="expandedRows"
                                            stripedRows
                                            sortMode="multiple"
                                            removableSort
                                            scrollable
                                            :loading="loadingTable"
                                            :reorderableColumns="true"
                                            @rowReorder="onRowReorder"
                                            class="text-sm"
                                        >
                                            <template #header>
                                                <div
                                                    class="border rounded-lg p-2 flex flex-wrap align-items-center justify-content-end justify-content-between gap-2"
                                                >
                                                    <Button
                                                        icon="pi pi-refresh"
                                                        rounded
                                                        raised
                                                    />
                                                    <IconField
                                                        iconPosition="left"
                                                    >
                                                        <InputIcon>
                                                            <i
                                                                class="pi pi-search"
                                                            />
                                                        </InputIcon>
                                                        <InputText
                                                            v-model="
                                                                filters[
                                                                    'global'
                                                                ].value
                                                            "
                                                            :placeholder="
                                                                $t('search')
                                                            "
                                                            class="rounded-lg"
                                                        />
                                                    </IconField>
                                                </div>
                                            </template>
                                            <template #empty>
                                                {{ $t("No items to show.") }}
                                            </template>
                                            <Column
                                                rowReorder
                                                headerStyle="width: 3rem"
                                                class="border-b"
                                                :reorderableColumn="false"
                                            />
                                            <Column
                                                selectionMode="multiple"
                                                headerStyle="width: 3rem "
                                                class="border-b"
                                            />
                                            <Column
                                                v-for="column in item.content
                                                    .titles"
                                                :field="column.field"
                                                :header="$t(column.title)"
                                                sortable
                                                :showFilterMenu="true"
                                                class="border-b"
                                            >
                                                <template #body="slotProps">
                                                    <!-- {{ console.log(slotProps) }} -->
                                                    {{
                                                        slotProps.data[
                                                            column.field
                                                        ]
                                                    }}
                                                </template>
                                            </Column>
                                            <Column
                                                expander
                                                frozen
                                                alignFrozen="right"
                                                style="width: 5rem"
                                                class="border-b"
                                            />
                                            <template #expansion="slotProps">
                                                <div class="p-3">
                                                    <h5>
                                                        Orders for
                                                        {{
                                                            slotProps.data.name
                                                        }}
                                                    </h5>
                                                </div>
                                            </template>
                                        </DataTable>
                                        <div
                                            ref="lastIntersection"
                                            class="-translate-y-96"
                                        />
                                    </template>
                                </template>
                            </TabPanel>
                        </TabView>
                    </template>
                </div>
            </div>
        </div>
    </div>
    <Modal />
    <ScrollTop />
    <TailwindIndicator />
    <Toast class="text-sm" />
</template>
