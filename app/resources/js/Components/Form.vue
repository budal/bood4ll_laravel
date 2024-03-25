<script setup lang="ts">
import { Ref, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { fetchData, isValidUrl } from "@/helpers";
import { trans, transChoice } from "laravel-vue-i18n";

import { DynamicDialogInstance } from "primevue/dynamicdialogoptions";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

import Table from "@/Components/Table.vue";
import Columns from "@/Components/Columns.vue";
import DropdownComponent from "@/Components/DropdownComponent.vue";

const props = defineProps<{
    components: any;
    id?: string | number;
    dialogRef?: DynamicDialogInstance;
}>();

const confirm = useConfirm();
const toast = useToast();

const loading = ref(false);
const formValue = ref<Record<string, any>>({});

const inputsWithError: Ref<{ [key: string]: boolean }> = ref({});

const go = (
    // event: any,
    callback: any,
    method: "get" | "post" | "put" | "patch" | "delete",
    data?: any,
    response?: any,
) => {
    if (callback) {
        if (callback.external === true) {
            window.location.href = isValidUrl(callback);
        } else {
            fetchData(callback, {
                complement: {
                    id: props.id,
                },
                method: method,
                data: data,
                onBefore: () => {
                    response: {
                        loading: true;
                    }
                },
                onError: (error: {
                    response: {
                        status: number;
                        statusText: string;
                        data: {
                            errors: any;
                            message: string;
                        };
                    };
                }) => {
                    inputsWithError.value = error.response.data.errors;

                    toast.add({
                        severity: "error",
                        summary: `${trans(error.response.statusText)} (${error.response.status})`,
                        detail: trans(
                            error.response.data.message || "Unknown error.",
                        ),
                        life: 3000,
                    });
                },
                onSuccess: (content: any) => {
                    if (content?.redirectUrl) router.visit(content.redirectUrl);
                },
                onFinish: () => {
                    response: {
                        loading: true;
                    }
                },
            });
        }
    }
};

const send = () => {
    fetchData(props.components.callback, {
        complement: {
            id: props.id,
        },
        method: props.components.method,
        data: formValue.value,
        onBefore: () => (loading.value = true),
        onError: (error: {
            response: {
                status: number;
                statusText: string;
                data: {
                    errors: any;
                    message: string;
                };
            };
        }) => {
            inputsWithError.value = error.response.data.errors;

            toast.add({
                severity: "error",
                summary: `${trans(error.response.statusText)} (${error.response.status})`,
                detail: trans(error.response.data.message || "Unknown error."),
                life: 3000,
            });
        },
        onSuccess: (content: any) => {
            inputsWithError.value = {};

            if (!props.id) {
                props.dialogRef?.close();
            }

            if (content.message)
                toast.add({
                    severity: content.type || "success",
                    summary: trans(content.title || "Confirmed"),
                    detail: transChoice(
                        content.message,
                        content.length || 1,
                        {},
                    ),
                    life: 3000,
                });

            if (content?.redirectUrl) router.visit(content.redirectUrl);
        },
        onFinish: () => (loading.value = false),
    });
};

const handleConfirm = (event: Event) => {
    if (props.components?.visible != false) {
        if (props.components?.confirm === true) {
            confirmDialog(event);
        } else {
            send();
        }
    } else {
        toast.add({
            severity: "error",
            summary: trans("Error"),
            detail: trans("You cant do this action."),
            life: 3000,
        });
    }
};

const confirmDialog = (event: Event) => {
    confirm.require({
        group: "popup",
        target:
            event.currentTarget instanceof HTMLElement
                ? event.currentTarget
                : undefined,
        message: trans(
            props.components.popup || "Do you want confirm this action?",
        ),
        icon: props.components.popupIcon || "pi pi-info-circle",
        rejectClass:
            props.components.popupCancelClass ||
            "p-button-secondary p-button-outlined p-button-sm",
        acceptClass:
            props.components.popupConfirmClass || "p-button-info p-button-sm",
        rejectLabel: trans(props.components.popupCancel || "No"),
        acceptLabel: trans(props.components.popupConfirm || "Yes"),
        accept: () => send(),
    });
};

const getFormValuesonLoad = () => {
    if (props.components.source) {
        fetchData(props.components.source, {
            complement: {
                id: props.id,
            },
            onBefore: () => {
                loading.value = true;
            },
            onSuccess: (content: never[]) => {
                formValue.value = content;
                // console.log(content);
            },
            onFinish: () => {
                loading.value = false;
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
};
</script>

<template>
    <DeferredContent @load="getFormValuesonLoad" aria-live="polite">
        <form
            v-if="components.showIf !== false"
            class="w-full space-y-6"
            @submit.prevent="send"
        >
            <div
                class="grid sm:gap-2"
                :class="{
                    'sm:grid-cols-1': components.cols == 1,
                    'sm:grid-cols-2': components.cols == 2,
                    'sm:grid-cols-3': components.cols == 3,
                    'sm:grid-cols-4': components.cols == 4,
                    'sm:grid-cols-5': components.cols == 5,
                    'sm:grid-cols-6': components.cols == 6,
                    'sm:grid-cols-7': components.cols == 7,
                    'sm:grid-cols-8': components.cols == 8,
                    'sm:grid-cols-9': components.cols == 9,
                    'sm:grid-cols-10': components.cols == 10,
                }"
            >
                <div :class="components.class">
                    <div
                        v-for="field in components.fields"
                        :class="{
                            'pt-6': field.type !== 'table',
                            'sm:col-span-1': field.span == 1,
                            'sm:col-span-2': field.span == 2,
                            'sm:col-span-3': field.span == 3,
                            'sm:col-span-4': field.span == 4,
                            'sm:col-span-5': field.span == 5,
                            'sm:col-span-6': field.span == 6,
                            'sm:col-span-7': field.span == 7,
                            'sm:col-span-8': field.span == 8,
                            'sm:col-span-9': field.span == 9,
                            'sm:col-span-10': field.span == 10,
                        }"
                    >
                        <Skeleton
                            v-if="loading === true && field.type !== 'links'"
                            height="3rem"
                            borderRadius="16px"
                        />
                        <template v-else>
                            <FloatLabel>
                                <Button
                                    v-if="field.type === 'button'"
                                    type="button"
                                    :disabled="field.disabled"
                                    :severity="field.severity || 'info'"
                                    :class="field.class"
                                    @click="go(field.callback, field.method)"
                                >
                                    <span
                                        class="material-symbols-rounded"
                                        v-text="field.icon"
                                    />
                                    {{ $t(field.label) }}
                                </Button>

                                <Calendar
                                    v-if="field.type === 'calendar'"
                                    :inputId="field.name"
                                    v-model="formValue[field.name]"
                                    :dateFormat="field.dateFormat"
                                    class="w-full"
                                    :invalid="
                                        inputsWithError.hasOwnProperty(
                                            field.name,
                                        )
                                    "
                                />

                                <DropdownComponent
                                    v-else-if="field.type === 'dropdown'"
                                    :id="props.id"
                                    v-model="formValue[field.name]"
                                    :source="field.source"
                                    :optionValue="field.optionValue || 'id'"
                                    :optionLabel="field.optionLabel || 'name'"
                                    :multiple="field.multiple"
                                    :invalid="
                                        inputsWithError.hasOwnProperty(
                                            field.name,
                                        )
                                    "
                                />

                                <InputText
                                    v-if="field.type === 'input'"
                                    :id="field.name"
                                    v-model="formValue[field.name]"
                                    class="w-full"
                                    :autocomplete="
                                        field.name === true ? field.name : 'off'
                                    "
                                    :invalid="
                                        inputsWithError.hasOwnProperty(
                                            field.name,
                                        )
                                    "
                                />

                                <InputMask
                                    v-else-if="field.type === 'mask'"
                                    :id="field.name"
                                    v-model="formValue[field.name]"
                                    :mask="field.mask"
                                    class="w-full"
                                    :invalid="
                                        inputsWithError.hasOwnProperty(
                                            field.name,
                                        )
                                    "
                                />

                                <Password
                                    v-if="field.type === 'password'"
                                    :input-id="field.name"
                                    v-model="formValue[field.name]"
                                    :input-class="'w-full'"
                                    class="w-full"
                                    :invalid="
                                        inputsWithError.hasOwnProperty(
                                            field.name,
                                        )
                                    "
                                    toggleMask
                                    :feedback="field.feedback ?? false"
                                />

                                <ToggleButton
                                    v-else-if="field.type === 'toggle'"
                                    :id="field.name"
                                    :inputId="field.name"
                                    v-model="formValue[field.name]"
                                    class="w-full"
                                    onIcon="pi pi-check"
                                    offIcon="pi pi-times"
                                    :onLabel="$t(field.label)"
                                    :offLabel="$t(field.label)"
                                    :invalid="
                                        inputsWithError.hasOwnProperty(
                                            field.name,
                                        )
                                    "
                                />

                                <label
                                    v-if="
                                        field.type !== 'button' &&
                                        field.type !== 'checkbox' &&
                                        field.type !== 'columns' &&
                                        field.type !== 'divider' &&
                                        field.type !== 'links' &&
                                        field.type !== 'table' &&
                                        field.type !== 'toggle'
                                    "
                                    :for="field.name"
                                >
                                    {{ $t(field.label || "") }}
                                </label>
                            </FloatLabel>

                            <template v-if="inputsWithError[field.name]">
                                <InlineMessage
                                    v-for="error in inputsWithError[field.name]"
                                    class="w-full mt-1"
                                    severity="error"
                                >
                                    <span class="text-xs">
                                        {{ error }}
                                    </span>
                                </InlineMessage>
                            </template>

                            <div
                                v-if="field.type === 'checkbox'"
                                class="flex align-items-center select-none"
                            >
                                <Checkbox
                                    v-model="formValue[field.name]"
                                    :inputId="field.name"
                                    :name="field.name"
                                    :value="true"
                                />
                                <label :for="field.name" class="ml-2">
                                    {{ $t(field.label || "") }}
                                </label>
                            </div>

                            <Columns
                                v-if="field.type === 'columns'"
                                :structure="field.structure"
                                :id="id"
                                :formValue="formValue"
                            />

                            <Divider
                                v-if="field.type === 'divider'"
                                layout="vertical"
                                class="hidden md:flex h-full"
                            >
                                <b>{{ field.label }}</b>
                            </Divider>
                            <Divider
                                v-if="field.type === 'divider'"
                                layout="horizontal"
                                class="flex md:hidden"
                                align="center"
                            >
                                <b>{{ field.label }}</b>
                            </Divider>

                            <div
                                v-if="field.type === 'links'"
                                class="flex justify-between underline text-sm"
                            >
                                <template v-for="link in field.values">
                                    <Link
                                        :href="isValidUrl(link.route) as string"
                                        :method="link.method || 'get'"
                                        as="button"
                                        type="button"
                                    >
                                        {{ $t(link.label) }}
                                    </Link>
                                </template>
                            </div>

                            <Table
                                v-if="field.type === 'table'"
                                :structure="field.structure"
                                :id="id"
                                :formValue="formValue"
                            />
                        </template>
                    </div>
                    <div
                        v-if="components.callback"
                        class="flex sticky bottom-0 p-2 mt-4 justify-end gap-2 rounded-xl backdrop-blur-sm"
                        :class="{
                            'sm:col-span-1': components.cols == 1,
                            'sm:col-span-2': components.cols == 2,
                            'sm:col-span-3': components.cols == 3,
                            'sm:col-span-4': components.cols == 4,
                            'sm:col-span-5': components.cols == 5,
                            'sm:col-span-6': components.cols == 6,
                            'sm:col-span-7': components.cols == 7,
                            'sm:col-span-8': components.cols == 8,
                            'sm:col-span-9': components.cols == 9,
                            'sm:col-span-10': components.cols == 10,
                        }"
                    >
                        <Button
                            type="button"
                            :disabled="loading === true"
                            :severity="
                                components.dialogConfirmClass || 'success'
                            "
                            :icon="components.dialogConfirmIcon || 'pi pi-send'"
                            :label="$t(components.dialogConfirm || 'Send')"
                            @click="handleConfirm"
                        />
                    </div>
                </div>
            </div>
        </form>
    </DeferredContent>
</template>
