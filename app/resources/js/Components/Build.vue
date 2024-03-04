<script setup lang="ts">
import { mkAttr, getData } from "@/helpers";
import { nextTick, ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";

import Table from "@/Components/Table.vue";
import Dropdown2 from "./Dropdown2.vue";

const props = withDefaults(
    defineProps<{
        component: any;
        id?: string | number;
    }>(),
    {},
);

const loading = ref(false);
const formValue = ref<Record<string, any>>({});

const onFormDataLoad = () => {
    if (props.component.source) {
        loading.value = true;

        const mkAttributes = mkAttr(
            props.component.sourceAttributes,
            ref({ id: props.id }).value,
        );

        let attributes = Object.assign(
            {},
            props.component.source.attributes,
            mkAttributes,
        );

        const route =
            typeof props.component.source === "object"
                ? props.component.source.route
                : props.component.source;

        getData({ route: route, attributes: attributes }).then((content) => {
            formValue.value = content;
            loading.value = false;
        });
    }
};
</script>

<template>
    <slot name="description" />

    <DeferredContent @load="onFormDataLoad" aria-live="polite">
        <form v-if="component.showIf !== false" class="space-y-6">
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
                            :formValue="formValue"
                        />
                    </template>
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
