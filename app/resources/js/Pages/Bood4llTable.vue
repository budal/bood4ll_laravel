<script setup lang="ts">
import { FilterMatchMode } from "primevue/api";

import { useToast } from "primevue/usetoast";
import { DataTableRowReorderEvent } from "primevue/datatable";

import NavBar from "@/Components/NavBar.vue";
import TailwindIndicator from "@/Components/TailwindIndicator.vue";

import { ref, onMounted } from "vue";
import { useIntersectionObserver } from "@vueuse/core";
import { Link } from "@inertiajs/vue3";

import { useConfirm } from "primevue/useconfirm";

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
const confirm = useConfirm();

const contentItems = ref(props.items?.data ?? []);

const dt = ref();
const lastIntersection = ref(null);
const nextPageURL = ref(null);
const loadingTable = ref(contentItems.value.length === 0);
const selectedItems = ref();
const expandedRows = ref([]);
const tableMenu = ref();
const columnsView = ref(false);
const columns = ref(props.data.content.titles);
const selectedColumns = ref(columns.value);

const tableMenuToggle = (event: MouseEvent) => {
    tableMenu.value.toggle(event);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const onToggleColumns = (val: string | any[]) => {
    selectedColumns.value = columns.value.filter((col: any) =>
        val.includes(col),
    );
};

///////////////

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    "country.name": { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    representative: { value: null, matchMode: FilterMatchMode.IN },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    verified: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const items = ref([
    {
        label: "New",
        icon: "pi pi-plus",
        command: () => {},
    },
    {
        label: "Delete",
        icon: "pi pi-trash",
        command: () => {},
    },
    {
        label: "Erase",
        icon: "pi pi-times",
        command: () => {
            confirm1();
        },
    },
    {
        separator: true,
    },
    {
        label: "Filter",
        icon: "pi pi-filter",
        items: [
            {
                label: "Active records",
                icon: "pi pi-check text-xs",
            },
            {
                label: "Trashed records",
            },
            {
                label: "All records",
                icon: "",
            },
        ],
    },
    {
        label: "Columns",
        icon: "pi pi-list",
        command: () => {
            columnsView.value = true;
        },
    },
    {
        separator: true,
    },
    {
        label: "Export CSV",
        icon: "pi pi-file-export",
        disabled: true,
        command: () => {
            exportCSV();
        },
    },
    // {
    //     separator: true,
    // },
    // {
    //     label: "Share",
    //     icon: "pi pi-share-alt",
    //     items: [
    //         {
    //             label: "Slack",
    //             icon: "pi pi-slack",
    //         },
    //         {
    //             label: "Whatsapp",
    //             icon: "pi pi-whatsapp",
    //         },
    //     ],
    // },
]);

const confirm1 = () => {
    confirm.require({
        group: "dialog",
        message: "Are you sure you want to proceed?",
        header: "Confirmation",
        icon: "pi pi-exclamation-triangle",
        rejectClass: "p-button-secondary p-button-outlined",
        rejectLabel: "Cancel",
        acceptLabel: "Save",
        accept: () => {
            toast.add({
                severity: "info",
                summary: "Confirmed",
                detail: "You have accepted",
                life: 3000,
            });
        },
        reject: () => {
            toast.add({
                severity: "error",
                summary: "Rejected",
                detail: "You have rejected",
                life: 3000,
            });
        },
    });
};

const confirm2 = (event: any) => {
    confirm.require({
        group: "popup",
        target: event.currentTarget,
        message: "Do you want to delete this record?",
        icon: "pi pi-info-circle",
        rejectClass: "p-button-secondary p-button-outlined p-button-sm",
        acceptClass: "p-button-danger p-button-sm",
        rejectLabel: "Cancel",
        acceptLabel: "Delete",
        accept: () => {
            toast.add({
                severity: "info",
                summary: "Confirmed",
                detail: "Record deleted",
                life: 3000,
            });
        },
        reject: () => {
            toast.add({
                severity: "error",
                summary: "Rejected",
                detail: "You have rejected",
                life: 3000,
            });
        },
    });
};

///////////////

async function getData(cursor: string | null) {
    try {
        const response = await fetch(cursor ?? window.location.href + "/json");
        return await response.json();
    } catch (error) {
        console.error(error);
    }
}

onMounted(() => {
    if (contentItems.value) {
        getData(null).then((content) => {
            contentItems.value = content.data;
            nextPageURL.value = content.next_page_url;
            loadingTable.value = false;
        });
    }
});

const onDataLoad = () => {
    if (contentItems.value) {
        getData(null).then((content) => {
            contentItems.value = content.data;
            nextPageURL.value = content.next_page_url;
            loadingTable.value = false;
        });
    }
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

const onRowReorder = (event: DataTableRowReorderEvent) => {
    contentItems.value = event.value;
    toast.add({
        severity: "success",
        summary: "Rows Reordered",
        detail: "This is a success toast message",
        life: 3000,
    });
};
</script>

<template>
    <div class="card relative min-h-screen">
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
            <NavBar class="max-w-7xl mx-auto py-1 px-2 sm:px-6 lg:px-8" />
        </nav>
        <div class="max-w-7xl mx-auto pt-8 px-2 sm:px-6 lg:px-8 space-y-6">
            <DeferredContent
                @load="onDataLoad"
                role="region"
                aria-live="polite"
                aria-label="Content loaded after page scrolled down"
            >
                <Card>
                    <template #title>{{ $t(data.label || "") }}</template>
                    <template #subtitle>{{
                        $t(data.description || "")
                    }}</template>
                    <template #content>
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
                                    class="flex flex-wrap items-center justify-content-end justify-between gap-2"
                                >
                                    <div>
                                        <Button
                                            icon="pi pi-ellipsis-v"
                                            rounded
                                            raised
                                            @click="tableMenuToggle"
                                            aria-haspopup="true"
                                            aria-controls="overlay_tmenu"
                                        />
                                        <TieredMenu
                                            ref="tableMenu"
                                            id="overlay_tmenu"
                                            :model="items"
                                            popup
                                        >
                                            <template #item="{ item, props }">
                                                <a
                                                    v-ripple
                                                    class="flex align-items-center"
                                                    v-bind="props.action"
                                                >
                                                    <span :class="item.icon" />
                                                    <span class="ml-2">
                                                        {{
                                                            $t(
                                                                item.label as string,
                                                            )
                                                        }}
                                                    </span>
                                                    <Badge
                                                        v-if="item.badge"
                                                        class="ml-auto"
                                                        :value="item.badge"
                                                    />
                                                    <span
                                                        v-if="item.shortcut"
                                                        class="ml-auto border-1 surface-border border-round surface-100 text-xs p-1"
                                                        >{{
                                                            item.shortcut
                                                        }}</span
                                                    >
                                                    <span
                                                        v-if="item.items"
                                                        class="pi pi-chevron-right ml-auto border-1 surface-border border-round surface-100 text-xs p-1"
                                                    />
                                                </a>
                                            </template>
                                        </TieredMenu>
                                        <Dialog
                                            v-model:visible="columnsView"
                                            modal
                                            header="Header"
                                            :style="{ width: '25rem' }"
                                        >
                                            <MultiSelect
                                                :modelValue="selectedColumns"
                                                :options="data.content.titles"
                                                optionLabel="header"
                                                @update:modelValue="
                                                    onToggleColumns
                                                "
                                                display="chip"
                                                :placeholder="
                                                    $t('Select columns')
                                                "
                                            />
                                        </Dialog>
                                    </div>
                                    <div class="flex gap-2">
                                        <IconField iconPosition="left">
                                            <InputIcon>
                                                <i class="pi pi-search" />
                                            </InputIcon>
                                            <InputText
                                                v-model="
                                                    filters['global'].value
                                                "
                                                :placeholder="$t('Search...')"
                                                class="pl-8"
                                            />
                                        </IconField>
                                    </div>
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
                    </template>
                    <template #footer>
                        <div ref="lastIntersection" class="-translate-y-96" />
                    </template>
                </Card>
            </DeferredContent>
        </div>
    </div>
    <Toast />
    <ConfirmDialog group="dialog" />
    <ConfirmPopup group="popup" />
    <ScrollTop />
    <TailwindIndicator />
</template>
