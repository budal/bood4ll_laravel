<script setup lang="ts">
import { ref } from "vue";
import { mkAttr, fetchData, mkRoute } from "@/helpers";

import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

import Table from "@/Components/Table.vue";
import Dropdown2 from "@/Components/Dropdown2.vue";
import { trans, transChoice } from "laravel-vue-i18n";

const props = defineProps<{
    component: any;
    id?: string | number;
}>();

const confirm = useConfirm();
const toast = useToast();

const loading = ref(false);
const formValue = ref<Record<string, any>>({});

const send = () => {
    loading.value = true;

    onFormDataLoad();

    console.log(
        props.component,
        // formValue.value,
        mkRoute(props.component, props.id),
    );

    if (props.component) {
        toast.add({
            severity: props.component.toastClass || "info",
            summary: trans(props.component.toastTitle || "Confirmed"),
            detail: transChoice(props.component.toast, 0, {}),
            life: 3000,
        });
    }
};

const handleConfirm = (event: Event) => {
    if (props.component?.visible != false) {
        if (props.component?.confirm === true) {
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
    if (props.component) {
        confirm.require({
            group: "popup",
            target:
                event.currentTarget instanceof HTMLElement
                    ? event.currentTarget
                    : undefined,
            message: trans(
                props.component.popup || "Do you want confirm this action?",
            ),
            icon: props.component.popupIcon || "pi pi-info-circle",
            rejectClass:
                props.component.popupCancelClass ||
                "p-button-secondary p-button-outlined p-button-sm",
            acceptClass:
                props.component.popupConfirmClass ||
                "p-button-info p-button-sm",
            rejectLabel: trans(props.component.popupCancel || "No"),
            acceptLabel: trans(props.component.popupConfirm || "Yes"),
            accept: () => send(),
        });
    }
};

const onFormDataLoad = () => {
    if (props.component.source) {
        fetchData(mkRoute(props.component, props.id), {
            onBefore: () => {
                loading.value = true;
            },
            onSuccess: (content: never[]) => {
                formValue.value = content;
            },
            onFinish: () => {
                loading.value = false;
            },
            onError: (error: { message: string }) => {
                console.log(error.message);
            },
        });
    }
};
</script>

<template>
    <DeferredContent @load="onFormDataLoad" aria-live="polite">
        <form v-if="component.showIf !== false" class="w-full space-y-6">
            <div
                class="grid sm:gap-2"
                :class="{
                    'sm:grid-cols-1': component.cols == 1,
                    'sm:grid-cols-2': component.cols == 2,
                    'sm:grid-cols-3': component.cols == 3,
                    'sm:grid-cols-4': component.cols == 4,
                    'sm:grid-cols-5': component.cols == 5,
                    'sm:grid-cols-6': component.cols == 6,
                    'sm:grid-cols-7': component.cols == 7,
                    'sm:grid-cols-8': component.cols == 8,
                    'sm:grid-cols-9': component.cols == 9,
                    'sm:grid-cols-10': component.cols == 10,
                }"
            >
                <div
                    v-for="field in component.fields"
                    :class="{
                        'pt-6': field.type != 'table',
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
                        v-if="loading == true"
                        height="3rem"
                        borderRadius="16px"
                    />
                    <template v-else>
                        <FloatLabel class="w-full">
                            <InputText
                                v-if="field.type === 'input'"
                                :id="field.name"
                                v-model="formValue[field.name]"
                                class="w-full"
                                :autocomplete="
                                    field.name === true ? field.name : 'off'
                                "
                                v-tooltip="''"
                            />
                            <Calendar
                                v-if="field.type === 'calendar'"
                                :inputId="field.name"
                                v-model="formValue[field.name]"
                                :dateFormat="field.dateFormat"
                                class="w-full"
                                v-tooltip="'Enter your username'"
                            />
                            <InputMask
                                v-else-if="field.type === 'mask'"
                                :id="field.name"
                                v-model="formValue[field.name]"
                                :mask="field.mask"
                                class="w-full"
                                v-tooltip="'Enter your username'"
                            />
                            <ToggleButton
                                v-else-if="field.type === 'toggle'"
                                :id="field.name"
                                :inputId="field.name"
                                v-model="formValue[field.name]"
                                class="w-full"
                                onIcon="pi pi-check"
                                offIcon="pi pi-times"
                                onLabel="On"
                                offLabel="Off"
                            />
                            <Dropdown2
                                v-else-if="field.type === 'dropdown'"
                                :id="field.name"
                                v-model="formValue[field.name]"
                                :url="field.source"
                                :urlAttributes="
                                    mkAttr(field.sourceAttributes, formValue)
                                "
                                :optionValue="field.optionValue || 'id'"
                                :optionLabel="field.optionLabel || 'name'"
                            />
                            <label
                                v-if="field.type !== 'toggle'"
                                :for="field.name"
                            >
                                {{ $t(field.label || "") }}</label
                            >
                        </FloatLabel>

                        <Table
                            v-if="field.type === 'table'"
                            :component="field.component"
                            :id="id"
                            :formValue="formValue"
                        />
                    </template>
                </div>
                <div
                    v-if="component.source"
                    class="flex sticky bottom-0 p-2 mt-4 justify-end gap-2 rounded-xl backdrop-blur-sm"
                    :class="{
                        'sm:col-span-1': component.cols == 1,
                        'sm:col-span-2': component.cols == 2,
                        'sm:col-span-3': component.cols == 3,
                        'sm:col-span-4': component.cols == 4,
                        'sm:col-span-5': component.cols == 5,
                        'sm:col-span-6': component.cols == 6,
                        'sm:col-span-7': component.cols == 7,
                        'sm:col-span-8': component.cols == 8,
                        'sm:col-span-9': component.cols == 9,
                        'sm:col-span-10': component.cols == 10,
                    }"
                >
                    <Button
                        type="button"
                        :disabled="loading == true"
                        :severity="component.dialogConfirmClass || 'success'"
                        :icon="component.dialogConfirmIcon || 'pi pi-send'"
                        :label="$t(component.dialogConfirm || 'Send')"
                        @click="handleConfirm"
                    />
                </div>
            </div>

            <!-- <div
                        v-if="props.routes[component.id]"
                        class="flex gap-4"
                        :class="
                            props.routes[component.id].buttonClass || 'justify-start'
                        "
                    ></div> -->
        </form>
    </DeferredContent>
</template>
