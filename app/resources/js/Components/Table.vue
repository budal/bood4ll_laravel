<script setup lang="ts">
import {
    ref,
    onMounted,
    computed,
    watch,
    markRaw,
    defineAsyncComponent,
    onBeforeUnmount,
} from "vue";
import { Link } from "@inertiajs/vue3";
import { isDefined, useIntersectionObserver } from "@vueuse/core";

import { useConfirm } from "primevue/useconfirm";
import { fetchData, mkRoute } from "@/helpers";

import { useToast } from "primevue/usetoast";
import { DataTableRowReorderEvent } from "primevue/datatable";
import { useDialog } from "primevue/usedialog";

import { trans, transChoice, wTrans } from "laravel-vue-i18n";
import { MenuItem } from "primevue/menuitem";

import { ReplacementsInterface } from "laravel-vue-i18n/interfaces/replacements";
import debounce from "lodash.debounce";

const props = defineProps<{
    component?: any;
    id?: string | number;
    formValue?: any;
}>();

const DialogBody = defineAsyncComponent(
    () => import("@/Components/DialogBody.vue"),
);

const DialogFooter = defineAsyncComponent(
    () => import("@/Components/DialogFooter.vue"),
);

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
const tableMenuItems = ref<MenuItem[]>([]);
const selectColumns = ref(false);
const tableColumns = ref(props.component.titles);
const selectedColumns = ref(tableColumns.value);
const search = ref(null);
const showItems = ref();
const routeUrlRef = ref(mkRoute(props.component.actions.index, props.id));
const routeUrlOptionsRef = ref({});

const tableMenuToggle = (event: MouseEvent) => {
    const _tableMenuItemsEdit: MenuItem[] = [
        {
            label: "Add",
            visible:
                props.component.actions.create?.visible != false &&
                isDefined(props.component.actions.destroy?.callback),
            disabled: props.component.actions.create?.disabled == true,
            icon: "pi pi-plus",
            command: () => {
                openDialog({
                    header: "Add unit",
                    action: props.component.actions.create,
                });
            },
        },
        {
            label: "Remove",
            visible:
                props.component.actions.destroy?.visible != false &&
                isDefined(props.component.actions.destroy?.callback) &&
                showItems.value !== "trashed",
            disabled:
                selectedItemsTotal.value.filter(
                    (item: { deleted_at: string }) => item.deleted_at === null,
                ).length < 1
                    ? true
                    : false,
            icon: "pi pi-trash",
            badge: selectedItemsTotal.value.filter(
                (item: { deleted_at: string }) => item.deleted_at === null,
            ).length,
            badgeClass: "warning",
            command: () => {
                confirmDialog({
                    header: "Remove",
                    callback: props.component.actions.destroy?.callback,
                    items: selectedItemsTotal.value.filter(
                        (item: { deleted_at: string }) =>
                            item.deleted_at === null,
                    ),
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
                isDefined(props.component.actions.restore?.callback) &&
                (showItems.value === "trashed" || showItems.value === "both"),
            disabled:
                selectedItemsTotal.value.filter(
                    (item: { deleted_at: string }) => item.deleted_at !== null,
                ).length < 1
                    ? true
                    : false,
            icon: "pi pi-replay",
            badge: selectedItemsTotal.value.filter(
                (item: { deleted_at: string }) => item.deleted_at !== null,
            ).length,
            badgeClass: "info",
            command: () => {
                confirmDialog({
                    header: "Restore",
                    callback: props.component.actions.restore?.callback,
                    items: selectedItemsTotal.value.filter(
                        (item: { deleted_at: string }) =>
                            item.deleted_at !== null,
                    ),
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
                isDefined(props.component.actions.forceDestroy?.callback) &&
                showItems.value === "trashed",
            disabled: selectedItemsTotal.value.length < 1 ? true : false,
            icon: "pi pi-times",
            badge: selectedItemsTotal.value.length,
            badgeClass: "danger",
            command: () => {
                confirmDialog({
                    header: "Erase",
                    callback: props.component.actions.forceDestroy?.callback,
                    items: selectedItemsTotal.value.filter(
                        (item: { deleted_at: string }) =>
                            item.deleted_at === null,
                    ),
                    message:
                        "Are you sure you want to erase the selected item?|Are you sure you want to erase the selected items?",
                    icon: "pi pi-times",
                    acceptClass: "p-button-danger",
                    acceptLabel: "Erase",
                });
            },
        },
    ];

    const _tableMenuItemsShow: MenuItem[] = [
        {
            label: "List",
            icon: "pi pi-eye",
            items: [
                {
                    label: "Active",
                    icon: (showItems.value == null
                        ? "pi pi-check text-xs"
                        : null) as string,
                    command: () => {
                        showItems.value = null;
                        onTableDataLoad();
                    },
                },
                {
                    label: "Trashed",
                    icon: (showItems.value == "trashed"
                        ? "pi pi-check text-xs"
                        : null) as string,
                    command: () => {
                        showItems.value = "trashed";
                        onTableDataLoad();
                    },
                },
                {
                    label: "Both",
                    icon: (showItems.value == "both"
                        ? "pi pi-check text-xs"
                        : null) as string,
                    command: () => {
                        showItems.value = "both";
                        onTableDataLoad();
                    },
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
                dt.value.exportCSV();
            },
        },
    ];

    const _tableMenuItemsComplementar: MenuItem[] = [];

    props.component.menu?.forEach(
        (item: {
            label: string;
            source: string;
            method: "get" | "post" | "put" | "patch" | "delete";
            data: any;
            visible: boolean;
            reload: boolean;
            icon: string;
        }) =>
            _tableMenuItemsComplementar.push({
                label: item.label,
                method: item.method,
                disabled: item.disabled,
                visible: item.visible === true,
                icon: item.icon,
                command: () => {
                    if (item.reload === true) {
                        routeUrlRef.value = mkRoute(item, props.id);

                        onTableDataLoad();
                    } else {
                        fetchData(mkRoute(item, props.id), {
                            id: props.id,
                            method: item.method,
                            data: item.data,
                            onBefore: () => {
                                toast.add({
                                    severity: "contrast",
                                    summary: trans("Loading"),
                                    detail: trans("Please wait..."),
                                    life: 3000,
                                });
                            },
                            onError: (error: { message: string }) => {
                                console.log(error.message);
                            },
                            onSuccess: (content: any) => {
                                toast.add({
                                    severity: content.type || "secondary",
                                    summary: trans(content.title),
                                    detail: transChoice(
                                        content.message,
                                        content.length,
                                        { total: content.length },
                                    ),
                                    life: 3000,
                                });

                                onTableDataLoad();
                            },
                        });
                    }
                },
            }),
    );

    tableMenuItems.value = [
        {
            label: "Refresh",
            icon: "pi pi-refresh",
            command: () => {
                onTableDataLoad();
            },
        },
        {
            separator: true,
        },
        ..._tableMenuItemsEdit,
        {
            separator: true,
            visible:
                _tableMenuItemsEdit.filter((item: any) => item.visible == true)
                    .length > 0,
        },
        ..._tableMenuItemsComplementar,
        {
            separator: true,
            visible:
                _tableMenuItemsComplementar.filter(
                    (item: any) => item.visible == true,
                ).length > 0,
        },
        ..._tableMenuItemsShow,
    ];

    tableMenu.value.toggle(event);
};

const selectedItemsTotal = computed(() => selectedItems.value);

const onToggleColumns = (val: string | any[]) => {
    selectedColumns.value = tableColumns.value.filter((col: any) =>
        val.includes(col),
    );
};

const confirmDialog = (options: {
    message: string;
    header?: string;
    callback?: any;
    items?: any;
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
            transChoice(options.message, options.items.length) ||
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
            console.log(options.callback, options.items);
            onTableDataLoad();
        },
    });
};

const openDialog = (options: {
    header: string;
    headerReplacement?: ReplacementsInterface;
    action?: any;
    id?: number | string;
}) => {
    dialog.open(DialogBody, {
        data: { action: options.action, id: options.id },
        props: {
            header: wTrans(
                options.header,
                options.headerReplacement,
            ) as unknown as string,
            style: {
                width: "70vw",
                height: "90vw",
            },
            breakpoints: {
                "960px": "75vw",
                "640px": "90vw",
            },
            modal: true,
            maximizable: true,
            draggable: false,
        },
    });
};

const onTableDataLoad = () => {
    selectedItems.value = [];
    loadingTable.value = true;

    routeUrlRef.value = {
        route: routeUrlRef.value.route,
        attributes: {
            ...routeUrlRef.value.attributes,
            ...{ search: search.value },
            ...{ showItems: showItems.value },
        },
    };

    fetchData(routeUrlRef.value, {
        id: props.id,
        onBefore: () => {
            selectedItems.value = [];
            loadingTable.value = true;
        },
        onSuccess: (content: { data: any; next_page_url: any }) => {
            contentItems.value = content.data;
            nextPageURL.value = content.next_page_url;
        },
        onFinish: () => {
            loadingTable.value = false;
        },
        onError: (error: { message: string }) => {
            console.log(error.message);
        },
    });
};

useIntersectionObserver(lastIntersection, ([{ isIntersecting }]) => {
    if (isIntersecting && nextPageURL.value !== null) {
        loadingTable.value = true;

        fetchData(nextPageURL.value, {
            id: props.id,
            onBefore: () => {
                loadingTable.value = true;
            },
            onSuccess: (content: { data: any; next_page_url: any }) => {
                nextPageURL.value = content.next_page_url;
                contentItems.value = [...contentItems.value, ...content.data];
            },
            onFinish: () => {
                loadingTable.value = false;
            },
            onError: (error: { message: string }) => {
                console.log(error.message);
            },
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

const debouncedWatch = debounce(() => {
    onTableDataLoad();
}, 500);

watch(search, debouncedWatch);

onBeforeUnmount(() => {
    debouncedWatch.cancel();
});
</script>

<template>
    <DeferredContent @load="onTableDataLoad" aria-live="polite">
        <DataTable
            ref="dt"
            :value="contentItems"
            dataKey="id"
            v-bind="
                (props.component.actions.destroy?.visible != false &&
                    isDefined(props.component.actions.destroy?.callback)) ||
                (props.component.actions.restore?.visible != false &&
                    isDefined(props.component.actions.restore?.callback)) ||
                (props.component.actions.forceDestroy?.visible != false &&
                    isDefined(props.component.actions.forceDestroy?.callback))
                    ? { selectionMode: 'multiple' }
                    : null
            "
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
            class="w-full"
        >
            <template #header>
                <div
                    class="flex flex-wrap items-center justify-content-end justify-between gap-2"
                >
                    <div>
                        <div class="sticky top-200">
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
                                        <span class="w-4" :class="item.icon" />
                                        <span class="ml-2">
                                            {{ $t(item.label as string) }}
                                        </span>
                                        <Badge
                                            v-if="item.badge"
                                            class="ml-auto"
                                            :severity="
                                                item.badgeClass || 'info'
                                            "
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
                    </div>
                    <div class="flex gap-2">
                        <IconField iconPosition="left">
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText
                                id="findTable"
                                v-model="search"
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
                v-bind="
                    (props.component.actions.destroy?.visible != false &&
                        isDefined(props.component.actions.destroy?.callback)) ||
                    (props.component.actions.restore?.visible != false &&
                        isDefined(props.component.actions.restore?.callback)) ||
                    (props.component.actions.forceDestroy?.visible != false &&
                        isDefined(
                            props.component.actions.forceDestroy?.callback,
                        ))
                        ? { selectionMode: 'multiple' }
                        : null
                "
            />
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
                    <p v-if="col.type == 'text'">
                        {{ slotProps.data[col.field] ?? "-" }}
                    </p>

                    <template
                        v-if="col.type == 'composite' && col.showIf !== false"
                    >
                        <template
                            v-if="col.values"
                            v-for="subitem in col.values"
                        >
                            <p
                                v-if="subitem.showIf !== false"
                                :class="subitem.class"
                            >
                                {{ $t(slotProps.data[subitem.field] ?? "-") }}
                            </p>
                        </template>
                        <template
                            v-if="slotProps.data[col.field]"
                            v-for="subitem in slotProps.data[col.field]"
                        >
                            <template v-for="option in col.options">
                                <p :class="option.class">
                                    {{
                                        subitem[option.field]
                                            ? $t(subitem[option.field])
                                            : "-"
                                    }}
                                </p>
                            </template>
                        </template>
                        <p v-if="slotProps.data[col.field]?.length == 0">-</p>
                    </template>
                    <p v-if="col.type == 'active'">
                        <i
                            class="pi"
                            :class="{
                                'pi-check-circle text-green-500':
                                    slotProps.data[col.field],
                                'pi-times-circle text-red-400':
                                    !slotProps.data[col.field],
                            }"
                        ></i>
                    </p>
                </template>
            </Column>
            <Column
                v-if="props.component.actions.edit?.visible != false"
                style="width: 1rem"
                frozen
                alignFrozen="right"
            >
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
        </DataTable>
        <div ref="lastIntersection" class="-translate-y-96" />
    </DeferredContent>
</template>
