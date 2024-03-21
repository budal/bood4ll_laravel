<script setup lang="ts">
import {
    ref,
    computed,
    watch,
    defineAsyncComponent,
    onBeforeUnmount,
} from "vue";
import { Link } from "@inertiajs/vue3";
import { isDefined, useIntersectionObserver } from "@vueuse/core";

import { useConfirm } from "primevue/useconfirm";
import { fetchData } from "@/helpers";

import { useToast } from "primevue/usetoast";
import { DataTableRowReorderEvent } from "primevue/datatable";
import { useDialog } from "primevue/usedialog";

import { trans, transChoice, wTrans } from "laravel-vue-i18n";
import { MenuItem } from "primevue/menuitem";

import { ReplacementsInterface } from "laravel-vue-i18n/interfaces/replacements";
import debounce from "lodash.debounce";

const props = defineProps<{
    structure?: any;
    id?: string | number;
    formValue?: any;
}>();

const DialogBody = defineAsyncComponent(
    () => import("@/Components/DialogBody.vue"),
);

const contentItems = ref<any[]>([]);

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
const tableColumns = ref(props.structure.titles);
const selectedColumns = ref(tableColumns.value);
const search = ref(null);
const listItems = ref();
const indexUrlRef = ref(props.structure.actions.index.source);

const tableMenuToggle = (event: MouseEvent) => {
    const activeItems = selectedItemsTotal.value.filter(
        (item: { deleted_at: string }) => item.deleted_at === null,
    );

    const deletedItems = selectedItemsTotal.value.filter(
        (item: { deleted_at: string }) => item.deleted_at !== null,
    );

    const _tableMenuItemsEdit: MenuItem[] = [
        {
            label: "Add",
            visible:
                props.structure.actions.create?.visible != false &&
                isDefined(props.structure.actions.destroy?.callback),
            disabled: props.structure.actions.create?.disabled == true,
            icon: "add",
            command: () => {
                openDialog({
                    header: "Add",
                    action: props.structure.actions.create,
                });
            },
        },
        {
            label: props.structure.actions.destroy?.title || "Remove",
            visible:
                props.structure.actions.destroy?.visible != false &&
                isDefined(props.structure.actions.destroy?.callback) &&
                listItems.value !== "trashed",
            disabled: activeItems.length < 1 ? true : false,
            icon: "remove",
            badge: activeItems.length,
            badgeClass: props.structure.actions.destroy?.class || "warning",
            command: () => {
                confirmDialog({
                    items: activeItems.length,
                    header: props.structure.actions.destroy?.title || "Remove",
                    message:
                        props.structure.actions.destroy?.message ||
                        "Are you sure you want to remove the selected item?|Are you sure you want to remove the selected items?",
                    icon: "pi pi-trash",
                    acceptClass: "p-button-warning",
                    acceptLabel:
                        props.structure.actions.destroy?.title || "Remove",
                    callback: () => {
                        toast.add({
                            severity:
                                props.structure.actions.destroy?.toastClass ||
                                "success",
                            summary: trans(
                                props.structure.actions.destroy?.title ||
                                    "Remove",
                            ),
                            detail: transChoice(
                                props.structure.actions.destroy?.toast ||
                                    "{0} Nothing to remove.|[1] Item removed successfully.|[2,*] :total items successfully removed.",
                                activeItems.length,
                                { ":total": activeItems.length },
                            ),
                            life: 3000,
                        });

                        console.log(
                            props.structure.actions.destroy?.callback,
                            activeItems,
                        );
                        onTableDataLoad();
                    },
                });
            },
        },
        {
            label: props.structure.actions.restore?.title || "Restore",
            visible:
                props.structure.actions.restore?.visible != false &&
                isDefined(props.structure.actions.restore?.callback) &&
                (listItems.value === "trashed" || listItems.value === "both"),
            disabled: deletedItems.length < 1 ? true : false,
            icon: "settings_backup_restore",
            badge: deletedItems.length,
            badgeClass: props.structure.actions.restore?.class || "info",
            command: () => {
                confirmDialog({
                    items: deletedItems.length,
                    header: props.structure.actions.restore?.title || "Restore",
                    message:
                        props.structure.actions.restore?.message ||
                        "Are you sure you want to restore the selected item?|Are you sure you want to restore the selected items?",
                    icon: "pi pi-replay",
                    acceptClass: "p-button-info",
                    acceptLabel:
                        props.structure.actions.restore?.title || "Restore",
                    callback: () => {
                        toast.add({
                            severity:
                                props.structure.actions.restore?.toastClass ||
                                "success",
                            summary: trans(
                                props.structure.actions.restore?.title ||
                                    "Remove",
                            ),
                            detail: transChoice(
                                props.structure.actions.restore?.toast ||
                                    "{0} Nothing to restore.|[1] Item restored successfully.|[2,*] :total items successfully restored.",
                                deletedItems.length,
                                { ":total": deletedItems.length },
                            ),
                            life: 3000,
                        });

                        console.log(
                            props.structure.actions.restore?.callback,
                            deletedItems,
                        );
                        onTableDataLoad();
                    },
                });
            },
        },
        {
            label: props.structure.actions.forceDestroy?.title || "Erase",
            visible:
                props.structure.actions.forceDestroy?.visible != false &&
                isDefined(props.structure.actions.forceDestroy?.callback) &&
                listItems.value === "trashed",
            disabled: activeItems.length < 1,
            icon: "delete_forever",
            badge: activeItems.length,
            badgeClass: props.structure.actions.forceDestroy?.class || "danger",
            command: () => {
                confirmDialog({
                    items: activeItems.length,
                    header:
                        props.structure.actions.forceDestroy?.title || "Erase",
                    message:
                        props.structure.actions.forceDestroy?.message ||
                        "Are you sure you want to erase the selected item?|Are you sure you want to erase the selected items?",
                    icon: "pi pi-times",
                    acceptClass: "p-button-danger",
                    acceptLabel:
                        props.structure.actions.forceDestroy?.title || "Erase",
                    callback: () => {
                        toast.add({
                            severity:
                                props.structure.actions.forceDestroy
                                    ?.toastClass || "success",
                            summary: trans(
                                props.structure.actions.forceDestroy?.title ||
                                    "Erase",
                            ),
                            detail: transChoice(
                                props.structure.actions.forceDestroy?.toast ||
                                    "{0} Nothing to erase.|[1] Item erased successfully.|[2,*] :total items successfully erased.",
                                activeItems.length,
                                { ":total": activeItems.length },
                            ),
                            life: 3000,
                        });

                        console.log(
                            props.structure.actions.forceDestroy?.callback,
                            activeItems,
                        );
                        onTableDataLoad();
                    },
                });
            },
        },
    ];

    const _tableMenuItemsShow: MenuItem[] = [
        {
            label: "Show items",
            icon: "rule",
            visible: isDefined(props.structure.actions.forceDestroy?.callback),
            items: [
                {
                    label: "Activated",
                    icon: (listItems.value == null
                        ? "check_small"
                        : null) as string,
                    command: () => {
                        listItems.value = null;
                        onTableDataLoad();
                    },
                },
                {
                    label: "Trashed",
                    icon: (listItems.value === "trashed"
                        ? "check_small"
                        : null) as string,
                    command: () => {
                        listItems.value = "trashed";
                        onTableDataLoad();
                    },
                },
                {
                    label: "Both",
                    icon: (listItems.value === "both"
                        ? "check_small"
                        : null) as string,
                    command: () => {
                        listItems.value = "both";
                        onTableDataLoad();
                    },
                },
            ],
        },
        {
            label: "Columns",
            icon: "view_column",
            command: () => {
                selectColumns.value = true;
            },
        },
        {
            separator: true,
            visible: props.structure.exportCSV === true,
        },
        {
            label: "Export CSV",
            icon: "csv",
            visible: props.structure.exportCSV === true,
            command: () => {
                dt.value.exportCSV();
            },
        },
    ];

    const _tableMenuItemsComplementar: MenuItem[] = [];

    props.structure.menu?.forEach(
        (item: {
            id: string | number | undefined;
            label: string;
            source: string;
            callback: string;
            method: "get" | "post" | "put" | "patch" | "delete";
            data: any;
            dialog: boolean;
            disabled: boolean;
            visible: boolean;
            icon: string;
            badgeClass: string;
            condition: string[];
            separator: boolean;
        }) => {
            if (item.separator === true) {
                _tableMenuItemsComplementar.push({
                    separator: true,
                    visible: true,
                    // _tableMenuItemsEdit.filter((item: any) => item.visible == true)
                    //     .length > 0,
                });
            } else {
                const conditionsKeys = item.condition
                    ? Object.keys(item.condition)
                    : [];

                const conditionsValues = item.condition
                    ? Object.values(item.condition)
                    : [];

                const items = selectedItemsTotal.value.filter((item) => {
                    return conditionsKeys.every(
                        (v, k) => item[v] === conditionsValues[k],
                    );
                });

                _tableMenuItemsComplementar.push({
                    label: item.label,
                    method: item.method,
                    disabled: item.condition && items.length < 1,
                    visible: item.visible,
                    icon: item.icon,
                    badge: item.condition ? items.length : null,
                    badgeClass: item.badgeClass,
                    command: () => {
                        if (item.source) {
                            if (item.dialog == true) {
                                openDialog({
                                    header: item.label,
                                    action: item,
                                    id: item.id,
                                });
                            } else {
                                indexUrlRef.value = item.source;

                                onTableDataLoad();
                            }
                        } else {
                            const data = items.map(
                                (item: { id: string | number }) => item.id,
                            );

                            fetchData(item.callback, {
                                complement: {
                                    id: props.id,
                                },
                                method: item.method,
                                data: { list: data },
                                onBefore: () => {
                                    toast.add({
                                        severity: "contrast",
                                        summary: trans("Loading"),
                                        detail: trans("Please wait..."),
                                        life: 3000,
                                    });
                                },
                                onError: (error: {
                                    response: {
                                        status: number;
                                        statusText: string;
                                        data: {
                                            message: string;
                                        };
                                    };
                                }) => {
                                    toast.add({
                                        severity: "error",
                                        summary: `${trans(error.response.statusText)} (${error.response.status})`,
                                        detail: trans(
                                            error.response.data.message,
                                        ),
                                        life: 3000,
                                    });
                                },
                                onSuccess: (content: {
                                    type:
                                        | "success"
                                        | "secondary"
                                        | "info"
                                        | "contrast"
                                        | "error"
                                        | "warn";
                                    title: string;
                                    message: string;
                                    length: number;
                                    replacements: ReplacementsInterface;
                                }) => {
                                    toast.add({
                                        severity: content.type || "secondary",
                                        summary: trans(content.title),
                                        detail: transChoice(
                                            content.message,
                                            content.length,
                                            content.replacements,
                                        ),
                                        life: 3000,
                                    });

                                    onTableDataLoad();
                                },
                            });
                        }
                    },
                });
            }
        },
    );

    tableMenuItems.value = [
        {
            label: "Refresh",
            icon: "refresh",
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
    header?: string;
    message: string;
    items?: number;
    icon?: string;
    rejectIcon?: string;
    rejectClass?: string;
    rejectLabel?: string;
    acceptIcon?: string;
    acceptClass?: string;
    acceptLabel?: string;
    callback?: Function;
}) => {
    confirm.require({
        group: "dialog",
        defaultFocus: "reject",
        header: trans(options.header || "Confirmation"),
        message:
            transChoice(options.message, options.items || 1) ||
            trans("Are you sure you want to proceed?"),
        icon: options.icon || "pi pi-exclamation-triangle",
        rejectIcon: options.rejectIcon,
        rejectClass:
            options.rejectClass || "p-button-secondary p-button-outlined",
        rejectLabel: trans(options.rejectLabel || "Cancel"),
        acceptIcon: options.acceptIcon || "pi pi-check",
        acceptClass: options.acceptClass || "p-button-primary",
        acceptLabel: trans(options.acceptLabel || "Confirm"),
        accept: () => {
            if (options.callback) options.callback();
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
        onClose: () => {
            onTableDataLoad();
        },
    });
};

const onTableDataLoad = () => {
    selectedItems.value = [];
    loadingTable.value = true;

    fetchData(indexUrlRef.value, {
        complement: {
            id: props.id,
            search: search.value,
            listItems: listItems.value,
        },
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
        onError: (error: {
            response: {
                status: number;
                statusText: string;
                data: {
                    message: string;
                };
            };
        }) => {
            toast.add({
                severity: "error",
                summary: `${trans(error.response.statusText)} (${error.response.status})`,
                detail: trans(error.response.data.message),
                life: 3000,
            });
        },
    });
};

useIntersectionObserver(lastIntersection, ([{ isIntersecting }]) => {
    if (isIntersecting && nextPageURL.value !== null) {
        loadingTable.value = true;

        fetchData(nextPageURL.value, {
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
            onError: (error: {
                response: {
                    status: number;
                    statusText: string;
                    data: {
                        message: string;
                    };
                };
            }) => {
                toast.add({
                    severity: "error",
                    summary: `${trans(error.response.statusText)} (${error.response.status})`,
                    detail: trans(error.response.data.message),
                    life: 3000,
                });
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

const onToggle = (
    source: any,
    method: "delete" | "get" | "post" | "put" | "patch",
    toggle: {
        data: {
            id: string | number;
            loading: boolean;
            icon: string;
            severity:
                | "success"
                | "secondary"
                | "info"
                | "danger"
                | "warning"
                | "help"
                | "contrast"
                | undefined;
            checked: boolean;
        };
    },
) => {
    fetchData(source, {
        complement: {
            id: props.id,
        },
        method: method,
        data: { list: [toggle.data.id] },
        onBefore: () => {
            toggle.data.loading = true;
        },
        onSuccess: (success: {
            type:
                | "success"
                | "secondary"
                | "info"
                | "contrast"
                | "warn"
                | "error"
                | undefined;
            title: string;
            message: string;
            deactivate: boolean;
            checked: boolean;
            length: number;
            replacements: ReplacementsInterface;
        }) => {
            const icon =
                success.checked === true ? "pi pi-check" : "pi pi-times";
            const severity = success.checked === true ? "success" : "danger";

            toggle.data.icon = icon;
            toggle.data.severity = severity;

            toggle.data.checked = success.deactivate === true ? false : true;

            toast.add({
                severity: success.type,
                summary: trans(success.title),
                detail: transChoice(
                    success.message,
                    success.length,
                    success.replacements,
                ),
                life: 3000,
            });
        },
        onFinish: () => {
            toggle.data.loading = false;
        },
        onError: (error: {
            response: {
                status: number;
                statusText: string;
                data: {
                    message: string;
                };
            };
        }) => {
            toast.add({
                severity: "error",
                summary: `${trans(error.response.statusText)} (${error.response.status})`,
                detail: trans(error.response.data.message),
                life: 3000,
            });
        },
    });
};
</script>

<template>
    <DeferredContent @load="onTableDataLoad" aria-live="polite">
        <DataTable
            ref="dt"
            :value="contentItems"
            dataKey="id"
            v-bind="
                (structure.actions.destroy?.visible != false &&
                    isDefined(structure.actions.destroy?.callback)) ||
                (structure.actions.restore?.visible != false &&
                    isDefined(structure.actions.restore?.callback)) ||
                (structure.actions.forceDestroy?.visible != false &&
                    isDefined(structure.actions.forceDestroy?.callback))
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
                structure.actions.reorder?.visible != false &&
                isDefined(structure.actions.reorder?.callback)
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
                                        <span
                                            class="material-symbols-rounded"
                                            v-text="item.icon"
                                        />
                                        <span class="ml-1">
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
                                        >
                                            {{ item.shortcut }}
                                        </span>
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
                                    :options="structure.titles"
                                    optionLabel="header"
                                    @update:modelValue="onToggleColumns"
                                    display="chip"
                                    :placeholder="$t('Select columns')"
                                />
                            </Dialog>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <InputGroup>
                            <InputGroupAddon>
                                <i class="pi pi-search"></i>
                            </InputGroupAddon>
                            <InputText
                                id="findTable"
                                v-model="search"
                                :placeholder="$t('Search...')"
                            />
                        </InputGroup>
                    </div>
                </div>
            </template>
            <template #empty>
                {{ $t("No items to show.") }}
            </template>
            <Column
                style="width: 1rem"
                v-bind="
                    structure.actions.index?.selectBoxes == true ||
                    (structure.actions.destroy?.visible != false &&
                        isDefined(structure.actions.destroy?.callback)) ||
                    (structure.actions.restore?.visible != false &&
                        isDefined(structure.actions.restore?.callback)) ||
                    (structure.actions.forceDestroy?.visible != false &&
                        isDefined(structure.actions.forceDestroy?.callback))
                        ? { selectionMode: 'multiple' }
                        : null
                "
            />
            <Column
                v-if="
                    structure.actions.reorder?.visible != false &&
                    isDefined(structure.actions.reorder?.callback)
                "
                rowReorder
                :reorderableColumn="false"
            />
            <Column
                v-for="(col, index) of selectedColumns"
                :field="col.field"
                :header="$t(col.header || '')"
                :key="col.field + '_' + index"
                sortable
                :showFilterMenu="true"
            >
                <template #body="slotProps">
                    <Avatar
                        v-if="col.type === 'avatar'"
                        :label="
                            slotProps.data[col.fallback].charAt(0).toUpperCase()
                        "
                        shape="circle"
                        size="large"
                    />
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

                    <template v-if="col.type == 'toggle'">
                        <Button
                            rounded
                            :loading="slotProps.data.loading"
                            :icon="
                                slotProps.data.checked == true
                                    ? slotProps.data.deleteOnly === true
                                        ? 'pi pi-trash'
                                        : 'pi pi-check'
                                    : 'pi pi-times'
                            "
                            :severity="
                                slotProps.data.checked == true
                                    ? slotProps.data.deleteOnly === true
                                        ? 'warning'
                                        : 'success'
                                    : 'danger'
                            "
                            @click="
                                onToggle(col.callback, col.method, slotProps)
                            "
                        />
                    </template>
                    <span
                        v-if="col.type == 'active'"
                        class="material-symbols-rounded"
                        :class="{
                            'text-green-500': slotProps.data[col.field],
                            'text-red-400': !slotProps.data[col.field],
                        }"
                        v-text="slotProps.data[col.field] ? 'check' : 'close'"
                    />
                </template>
            </Column>
            <Column
                v-if="
                    structure.actions.edit?.visible != false &&
                    isDefined(structure.actions.edit)
                "
                frozen
                alignFrozen="right"
                style="width: 1rem"
            >
                <template #body="{ data }">
                    <Button
                        class="material-symbols-rounded"
                        type="button"
                        v-text="'edit_square'"
                        text
                        size="small"
                        @click="
                            openDialog({
                                header: `Edit`,
                                action: structure.actions.edit,
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
