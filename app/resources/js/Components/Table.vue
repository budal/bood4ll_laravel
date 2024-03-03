<script setup lang="ts">
import {
    ref,
    onMounted,
    computed,
    watch,
    markRaw,
    defineAsyncComponent,
} from "vue";
import { Link, router } from "@inertiajs/vue3";
import { isDefined, useIntersectionObserver } from "@vueuse/core";

import { useConfirm } from "primevue/useconfirm";
import { isValidUrl, getData, formatRouteWithID } from "@/helpers";

import { FilterMatchMode } from "primevue/api";
import { useToast } from "primevue/usetoast";
import { DataTableRowReorderEvent } from "primevue/datatable";
import { useDialog } from "primevue/usedialog";

import { trans, transChoice, wTrans } from "laravel-vue-i18n";
import { MenuItem } from "primevue/menuitem";
import { provide } from "vue";

import Structure from "@/Components/Structure.vue";
import { ReplacementsInterface } from "laravel-vue-i18n/interfaces/replacements";

const props = defineProps<{
    component?: any;
    urlAttributes?: any;
}>();

const contentItems = ref(props.component.data?.data ?? []);

const dialog = useDialog();
const toast = useToast();
const confirm = useConfirm();

const dt = ref();
const lastIntersection = ref(null);
const nextPageURL = ref(null);
const loadingTable = ref(contentItems.value.length === 0);
const selectedItems = ref([]);
const expandedRows = ref([]);
const tableMenu = ref();
const selectColumns = ref(false);
const tableColumns = ref(props.component.titles);
const selectedColumns = ref(tableColumns.value);
const structureData = ref([]);

const tableMenuToggle = (event: MouseEvent) => {
    tableMenu.value.toggle(event);
};

const selectedItemsTotal = computed(() => selectedItems.value);

const _tableMenuItemsEdit: MenuItem[] = [
    {
        label: "Add",
        visible: props.component.actions.create?.visible != false,
        disabled: props.component.actions.create?.disabled == true,
        icon: "pi pi-plus",
        command: () => {
            openDialog({
                header: "Add unit",
                action: props.component.actions.create,
            });

            // router.visit(
            //     isValidUrl(props.component.actions.create?.callback),
            //     {
            //         preserveState: true,
            //         preserveScroll: true,
            //     },
            // );
        },
    },
    {
        label: "Remove",
        visible:
            props.component.actions.destroy?.visible != false &&
            isDefined(props.component.actions.destroy?.callback),
        disabled: props.component.actions.destroy?.disabled == true,
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
        visible:
            props.component.actions.restore?.visible != false &&
            isDefined(props.component.actions.restore?.callback),
        disabled: props.component.actions.restore?.disabled == true,
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
        visible:
            props.component.actions.forceDestroy?.visible != false &&
            isDefined(props.component.actions.forceDestroy?.callback),
        disabled: props.component.actions.forceDestroy?.disabled == true,

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
            isDefined(props.component.actions.create?.callback) ||
            isDefined(props.component.actions.destroy?.callback) ||
            isDefined(props.component.actions.restore?.callback) ||
            isDefined(props.component.actions.forceDestroy?.callback),
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
        label: "Columns",
        icon: "pi pi-list",
        command: () => {
            selectColumns.value = true;
        },
    },
    {
        separator: true,
        visible: props.component.exportCSV === true,
    },
    {
        label: "Export CSV",
        icon: "pi pi-file-export",
        visible: props.component.exportCSV === true,
        command: () => {
            exportCSV();
        },
    },
];

let _tableMenuItemsComplementar: MenuItem[] = [];

props.component.menu?.forEach(
    (item: {
        label: string;
        route: string;
        method: string;
        showIf: boolean;
        icon: string;
    }) =>
        _tableMenuItemsComplementar.push({
            label: item.label,
            url: isValidUrl(item.route) as string,
            method: item.method,
            disabled: isValidUrl(item.route) ? false : true,
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
    selectedColumns.value = tableColumns.value.filter((col: any) =>
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

const openDialog = (options: {
    header: string;
    headerReplacement?: ReplacementsInterface;
    action?: any;
    id?: number | string;
}) => {
    dialog.open(FormDialog, {
        data: { action: options.action, id: options.id },
        props: {
            header: wTrans(
                options.header,
                options.headerReplacement,
            ) as unknown as string,
            style: {
                width: "90vw",
            },
            breakpoints: {
                "1080px": "75vw",
                "640px": "90vw",
            },
            modal: true,
            maximizable: true,
            draggable: false,
        },
        templates: {
            footer: markRaw(FooterDemo),
        },
        onClose: (options: any) => {
            const data = options.data;
            if (data) {
                const buttonType = data.buttonType;
                const summary_and_detail = buttonType
                    ? {
                          summary: "No Product Selected",
                          detail: `Pressed '${buttonType}' button`,
                      }
                    : { summary: "Product Selected", detail: data.name };

                toast.add({
                    severity: "info",
                    ...summary_and_detail,
                    life: 3000,
                });
            }
        },
    });
};

const routeUrl = {
    route: props.component.actions.index.route,
    attributes: props.urlAttributes,
};

if (typeof props.component.actions.index.route === "object") {
    routeUrl.attributes.push(props.urlAttributes);
}

const onTableDataLoad = () => {
    // if (!contentItems.value)
    {
        getData(routeUrl).then((content) => {
            contentItems.value = content.data;
            nextPageURL.value = content.next_page_url;
            loadingTable.value = false;
        });
    }
};

const onStrutureDataLoad = () => {
    getData(props.component.actions.index.route).then((content) => {
        structureData.value = content.data;
    });
};

useIntersectionObserver(lastIntersection, ([{ isIntersecting }]) => {
    if (isIntersecting && nextPageURL.value !== null) {
        loadingTable.value = true;

        getData(nextPageURL.value).then((content) => {
            nextPageURL.value = content.next_page_url;
            contentItems.value = [...contentItems.value, ...content.data];
            loadingTable.value = false;
        });
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

const FormDialog = defineAsyncComponent(
    () => import("@/Components/Dialog.vue"),
);

const FooterDemo = defineAsyncComponent(
    () => import("@/Components/_useless/FooterDemo.vue"),
);
</script>

<template>
    <DeferredContent @load="onTableDataLoad" aria-live="polite">
        <DataTable
            ref="dt"
            :value="contentItems"
            dataKey="id"
            v-model:selection="selectedItems"
            v-model:expandedRows="expandedRows"
            stripedRows
            sortMode="multiple"
            rowHover
            removableSort
            scrollable
            :loading="loadingTable"
            :rowReorder="
                component.actions.reorder?.visible != false &&
                isDefined(component.actions.reorder?.callback)
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
                                    class="flex align-items-center"
                                    v-bind="props.action"
                                    v-ripple
                                >
                                    <span :class="item.icon" />
                                    <span class="ml-2">
                                        {{ $t(item.label as string) }}
                                    </span>
                                    <Badge
                                        v-if="item.badge"
                                        class="ml-auto"
                                        :value="item.badge"
                                    />
                                    <span
                                        v-if="item.shortcut"
                                        class="ml-auto border-1 surface-border border-round surface-100 text-xs p-1"
                                        >{{ item.shortcut }}</span
                                    >
                                    <span
                                        v-if="item.items"
                                        class="pi pi-chevron-right ml-auto border-1 surface-border border-round surface-100 text-xs p-1"
                                    />
                                </a>
                            </template>
                        </TieredMenu>
                        <Dialog
                            v-model:visible="selectColumns"
                            modal
                            :header="trans('Columns')"
                            :style="{ width: '25rem' }"
                        >
                            <MultiSelect
                                :modelValue="selectedColumns"
                                :options="component.titles"
                                optionLabel="header"
                                @update:modelValue="onToggleColumns"
                                display="chip"
                                :placeholder="$t('Select columns')"
                            />
                        </Dialog>
                    </div>
                    <div class="flex gap-2">
                        <IconField iconPosition="left">
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText
                                id="findTable"
                                v-model="filters['global'].value"
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
            <Column selectionMode="multiple" headerStyle="width: 1rem"></Column>
            <Column
                v-if="
                    component.actions.reorder?.visible != false &&
                    isDefined(component.actions.reorder?.callback)
                "
                rowReorder
                headerStyle="width: 3rem"
                class="border-b"
                :reorderableColumn="false"
            />
            <Column
                v-for="(col, index) of selectedColumns"
                :field="col.field"
                :header="$t(col.header || '')"
                :key="col.field + '_' + index"
                sortable
                :showFilterMenu="true"
                class="border-b"
            >
                <template #body="slotProps">
                    {{ slotProps.data[col.field] }}
                </template>
            </Column>
            <Column style="width: 1rem" frozen alignFrozen="right">
                <template #body="{ data }">
                    <Button
                        type="button"
                        icon="pi pi-pencil"
                        text
                        size="small"
                        @click="
                            openDialog({
                                header: `Edit ':unit'`,
                                headerReplacement: { unit: data.shortpath },
                                action: props.component.actions.edit,
                                id: data.id,
                            })
                        "
                    />
                </template>
            </Column>
            <!-- 
            <Column
                v-if="component.actions.edit.embbeded === true"
                expander
                frozen
                alignFrozen="right"
                style="width: 1rem"
                class="border-b"
            />
            <template
                v-if="component.actions.edit.embbeded === true"
                #expansion="slotProps"
            >
                <Structure
                    :component="component.actions.edit.form.component"
                    :tabs="component.actions.edit.form.tabs"
                    :buildRoute="
                        isValidUrl({
                            route: component.actions.edit.route,
                            attributes: [slotProps.data.id],
                        })
                    "
                />
            </template>
         -->
        </DataTable>
        <div ref="lastIntersection" class="-translate-y-96" />
    </DeferredContent>
</template>
