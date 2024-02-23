<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { useIntersectionObserver } from "@vueuse/core";

import { useConfirm } from "primevue/useconfirm";
import { isValidUrl, formatRouteWithID } from "@/helpers";

import { FilterMatchMode } from "primevue/api";
import { useToast } from "primevue/usetoast";
import { DataTableRowReorderEvent } from "primevue/datatable";

import NavBar from "@/Components/NavBar.vue";
import TailwindIndicator from "@/Components/TailwindIndicator.vue";
import { trans, transChoice } from "laravel-vue-i18n";
import { watch } from "vue";
import { MenuItem } from "primevue/menuitem";

const props = withDefaults(
    defineProps<{
        component?: any;
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

const contentItems = ref(props.data?.data ?? []);

const dt = ref();
const lastIntersection = ref(null);
const nextPageURL = ref(null);
const loadingTable = ref(contentItems.value.length === 0);
const selectedItems = ref([]);
const expandedRows = ref([]);
const tableMenu = ref();
const columnsView = ref(false);
const columns = ref(props.component.titles);
const selectedColumns = ref(columns.value);

const tableMenuToggle = (event: MouseEvent) => {
    tableMenu.value.toggle(event);
};

const selectedItemsTotal = computed(() => selectedItems.value);

watch(selectedItemsTotal, () => {
    console.log(selectedItemsTotal.value.length < 1);
});

const _tableMenuItemsEdit: MenuItem[] = [
    {
        label: "Add",
        url: isValidUrl(props.component.routes.createRoute?.route),
        visible: props.component.routes.createRoute?.showIf === true,
        icon: "pi pi-plus",
        command: () => {},
    },
    {
        label: "Remove",
        url: isValidUrl(props.component.routes.destroyRoute?.route),
        visible: props.component.routes.destroyRoute?.showIf === true,
        icon: "pi pi-trash",
        command: () => {
            confirmDialog({
                header: "Remove",
                message:
                    "Are you sure you want to remove the selected item?|Are you sure you want to remove the selected items?",
                icon: "pi pi-trash",
                acceptClass: "p-button-warning",
                acceptLabel: "Remove",
            });
        },
    },
    {
        label: "Restore",
        url: isValidUrl(props.component.routes.restoreRoute?.route),
        visible: props.component.routes.restoreRoute?.showIf === true,
        icon: "pi pi-replay",
        command: () => {
            confirmDialog({
                header: "Restore",
                message:
                    "Are you sure you want to restore the selected item?|Are you sure you want to restore the selected items?",
                icon: "pi pi-replay",
                acceptClass: "p-button-info",
                acceptLabel: "Restore",
            });
        },
    },
    {
        label: "Erase",
        url: isValidUrl(props.component.routes.forceDestroyRoute?.route),
        visible: props.component.routes.forceDestroyRoute?.showIf === true,
        icon: "pi pi-times",
        command: () => {
            confirmDialog({
                header: "Erase",
                message:
                    "Are you sure you want to erase the selected item?|Are you sure you want to erase the selected items?",
                icon: "pi pi-times",
                acceptClass: "p-button-danger",
                acceptLabel: "Erase",
            });
        },
    },
    {
        separator: true,
        visible:
            props.component.routes.createRoute?.showIf === true ||
            props.component.routes.destroyRoute?.showIf === true ||
            props.component.routes.restoreRoute?.showIf === true ||
            props.component.routes.forceDestroyRoute?.showIf === true,
    },
];

const _tableMenuItemsShow: MenuItem[] = [
    {
        label: "Filters",
        icon: "pi pi-filter",
        items: [
            {
                label: "Active only",
                icon: "pi pi-check text-xs",
            },
            {
                label: "Trashed only",
            },
            {
                label: "All records",
                icon: "",
            },
        ],
    },
    {
        key: "columns",
        label: "Columns",
        icon: "pi pi-list",
        command: () => {
            columnsView.value = true;
        },
    },
    {
        separator: true,
        visible: props.component.exportCSV === true,
    },
    {
        key: "export_csv",
        label: "Export CSV",
        icon: "pi pi-file-export",
        visible: props.component.exportCSV === true,
        command: () => {
            exportCSV();
        },
    },
];

console.log(props.component.menu);

let _tableMenuItemsComplementar: MenuItem[] = [];

props.component.menu.forEach(
    (item: {
        label: string;
        route: string;
        method: string;
        showIf: boolean;
        icon: string;
    }) =>
        _tableMenuItemsComplementar.push({
            label: item.label,
            url: isValidUrl(item.route),
            method: item.method,
            visible: item.showIf === true,
            icon: item.icon,
        }),
);

_tableMenuItemsComplementar.push({
    separator: true,
    visible:
        _tableMenuItemsComplementar.filter((item) => item.visible === true)
            .length > 0,
});

const tableMenuItems = ref([
    ..._tableMenuItemsEdit,
    ..._tableMenuItemsComplementar,
    ..._tableMenuItemsShow,
]);

const onToggleColumns = (val: string | any[]) => {
    selectedColumns.value = columns.value.filter((col: any) =>
        val.includes(col),
    );
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmDialog = (options: {
    message: string;
    header?: string;
    icon?: string;
    rejectIcon?: string;
    rejectClass?: string;
    rejectLabel?: string;
    acceptIcon?: string;
    acceptClass?: string;
    acceptLabel?: string;
}) => {
    confirm.require({
        group: "dialog",
        message:
            transChoice(options.message, 1) ||
            trans("Are you sure you want to proceed?"),
        header: trans(options.header || "Confirmation"),
        icon: options.icon || "pi pi-exclamation-triangle",
        rejectIcon: options.rejectIcon,
        rejectClass:
            options.rejectClass || "p-button-secondary p-button-outlined",
        rejectLabel: trans(options.rejectLabel || "Cancel"),
        acceptIcon: options.acceptIcon || "pi pi-check",
        acceptClass: options.acceptClass || "p-button-primary",
        acceptLabel: trans(options.acceptLabel || "Confirm"),
        defaultFocus: "reject",
        accept: () => {
            toast.add({
                severity: "info",
                summary: "Confirmed",
                detail: "You have accepted",
                life: 3000,
            });
        },
    });
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
                    <template #title>{{ $t(component.label || "") }}</template>
                    <template #subtitle>{{
                        $t(component.description || "")
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
                            :rowReorder="
                                props.component.routes.reorderRoute?.showIf ===
                                true
                            "
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
                                            :model="tableMenuItems"
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
                                            header="Columns"
                                            :style="{ width: '25rem' }"
                                        >
                                            <MultiSelect
                                                :modelValue="selectedColumns"
                                                :options="component.titles"
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
                                v-if="
                                    props.component.routes.reorderRoute
                                        ?.showIf === true
                                "
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
    <ConfirmDialog group="dialog" :draggable="false" />
    <ConfirmPopup group="popup" />
    <ScrollTop />
    <TailwindIndicator />
</template>
