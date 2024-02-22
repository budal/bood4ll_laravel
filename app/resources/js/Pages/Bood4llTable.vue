<script setup lang="ts">
import { FilterMatchMode } from "primevue/api";

import { useToast } from "primevue/usetoast";
import { DataTableRowReorderEvent } from "primevue/datatable";

import NavBar from "@/Components/NavBar.vue";
import TailwindIndicator from "@/Components/TailwindIndicator.vue";

import { ref, onMounted } from "vue";
import { useIntersectionObserver } from "@vueuse/core";
import { Link } from "@inertiajs/vue3";

const props = withDefaults(
    defineProps<{
        data?: any;
        items?: any;
        isGuest?: boolean;
        isModal?: boolean;
        title?: string;
        form?: any;
        routes?: any;
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

const contentItems = ref(props.items?.data ?? []);

const dt = ref();
const lastIntersection = ref(null);
const nextPageURL = ref(null);
const loadingTable = ref(contentItems.value.length === 0);
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
    toast.add({
        severity: "success",
        summary: "Rows Reordered",
        detail: "This is a success toast message",
        life: 3000,
    });
};

const columns = ref(props.data.content.titles);

const selectedColumns = ref(columns.value);

const onToggle = (val: string | any[]) => {
    selectedColumns.value = columns.value.filter((col: any) =>
        val.includes(col),
    );
};

const exportCSV = () => {
    dt.value.exportCSV();
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
    if (contentItems.value) {
        getData(null).then((content) => {
            contentItems.value = content.data;
            nextPageURL.value = content.next_page_url;
            loadingTable.value = false;
        });
    }
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
                <div class="border rounded-lg">
                    <DataTable
                        ref="dt"
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
                    >
                        <template #header>
                            <div
                                class="border rounded-lg p-2 flex flex-wrap items-center justify-content-end justify-between gap-2"
                            >
                                <div>
                                    <Button
                                        icon="pi pi-refresh"
                                        rounded
                                        raised
                                    />

                                    <Button
                                        icon="pi pi-external-link"
                                        label="Export"
                                        @click="exportCSV()"
                                    />

                                    <MultiSelect
                                        :modelValue="selectedColumns"
                                        :options="data.content.titles"
                                        optionLabel="header"
                                        @update:modelValue="onToggle"
                                        display="chip"
                                        :placeholder="$t('Select columns')"
                                    />
                                </div>

                                <IconField iconPosition="left">
                                    <InputIcon>
                                        <i class="pi pi-search" />
                                    </InputIcon>
                                    <InputText
                                        v-model="filters['global'].value"
                                        :placeholder="$t('search')"
                                        class="pl-8 rounded-lg"
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
                            v-for="(col, index) of selectedColumns"
                            :field="col.field"
                            :header="$t(col.header)"
                            :key="col.field + '_' + index"
                            sortable
                            :showFilterMenu="true"
                            class="border-b"
                        >
                            <template #body="slotProps">
                                {{ slotProps.data[col.field] }}
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
                                    {{ slotProps.data.shortpath }}
                                </h5>
                            </div>
                        </template>
                    </DataTable>
                    <div ref="lastIntersection" class="-translate-y-96" />
                </div>
            </div>
        </div>
    </div>
    <ScrollTop />
    <TailwindIndicator />
    <Toast />
</template>
