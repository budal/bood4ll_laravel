<script setup lang="ts">
import { isValidUrl, toast } from "@/helpers";
import { Ref, nextTick, ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";

import { inject, onMounted } from "vue";

import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import type { DynamicDialogInstance } from "primevue/dynamicdialogoptions";

import Table from "@/Components/Table.vue";

const props = withDefaults(
    defineProps<{
        component: any;
        buildRoute?: string;

        ///////
        data?: any;
        formRoute?: any;
        create?: any;
        form?: any;
        routes?: any;
        shortcutKey?: string;
        tabs?: boolean;
        status?: string | null;
        statusTheme?: string | null;
    }>(),
    {
        tabs: true,
        statusTheme: "info",
    },
);

interface FormItems {
    [key: string]: string;
}

const tabs = ref(props.component);
const fields = ref([]);

let formItems: FormItems = {};

// props.component.forEach((form: any) => {
// form.fields.forEach((field: any) => {
// console.log(field);
// fields.forEach((field: any) => {
//         formItems[field.name] = props.data
//             ? props?.data[field.name] || ""
//             : "";
// });
//     });
// });

const jsForm = useForm(formItems);

const sendForm = (formId: string) => {
    // console.log(jsForm.data());
    // return false;

    jsForm.submit(props.routes[formId].method, props.routes[formId].route, {
        preserveScroll: true,
        onSuccess: () => {
            toast();
        },
        onError: () => {
            toast();

            console.log(jsForm.errors);

            // if (jsForm.errors.password) {
            //     jsForm.reset('password', 'password_confirmation');
            //     passwordInput.value?.focus();
            // }
            // if (jsForm.errors.current_password) {
            //     jsForm.reset('current_password');
            //     currentPasswordInput.value?.focus();
            // }
        },
        onFinish: () => {
            props.routes[formId].reset === true
                ? props.routes[formId].fieldsToReset
                    ? jsForm.reset(...props.routes[formId].fieldsToReset)
                    : jsForm.reset()
                : false;
        },
    });
};

// let component = ref("");

// const changeTab = (item: any) => {
//     component.value = item || null;
// };

// modal
let modalInfo = ref();

const showModal = ref(false);

const passwordInput = ref<HTMLInputElement | null>(null);

const modalForm = useForm({
    password: "",
});

const openModal = (field: any) => {
    modalInfo.value = field;
    showModal.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const closeModal = () => {
    showModal.value = false;

    modalForm.reset();
};

const submitModal = () => {
    modalForm.submit(
        modalInfo.value.method,
        isValidUrl(modalInfo.value.route) as string,
        {
            preserveScroll: true,
            preserveState: modalInfo.value.preserveState,

            onSuccess: () => {
                toast();
                closeModal();
            },
            onError: () => {
                toast();
                () => passwordInput.value?.focus();
            },
            onFinish: () => {
                modalForm.reset();
            },
        },
    );
};

const value = ref(null);
const checked = ref(false);

async function getData(route: any) {
    try {
        const response = await fetch(isValidUrl(route) as string);
        return await response.json();
    } catch (error) {
        console.error(error);
    }
}

const onFormDataLoad = () => {
    if (props.buildRoute) {
        getData(props.buildRoute).then((content) => {
            console.log(content);
        });
        //     Array.from(formRef).find((item) => item.id === id);
        //     Array.from(formRef).some((item) => item.id === id) === false
    }
};
</script>

<template>
    <slot name="description" />

    <DeferredContent
        @load="onFormDataLoad"
        role="region"
        aria-live="polite"
        aria-label="Content loaded after page scrolled down"
    >
        <form
            v-if="component.showIf !== false"
            @submit.prevent="sendForm(component.id)"
            class="space-y-6"
        >
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
                    <FloatLabel v-if="field.type === 'input'" class="w-full">
                        <InputText
                            :id="field.name"
                            v-model="value"
                            class="w-full"
                            v-tooltip="'Enter your username'"
                        />
                        <label :for="field.name">
                            {{ $t(field.label as string) }}</label
                        >
                    </FloatLabel>
                    <FloatLabel v-if="field.type === 'calendar'" class="w-full">
                        <Calendar
                            :id="field.name"
                            :dateFormat="field.dateFormat"
                            class="w-full"
                            v-tooltip="'Enter your username'"
                        />
                        <label :for="field.name">
                            {{ $t(field.label as string) }}</label
                        >
                    </FloatLabel>
                    <FloatLabel
                        v-else-if="field.type === 'mask'"
                        class="w-full"
                    >
                        <InputMask
                            :id="field.name"
                            :mask="field.mask"
                            class="w-full"
                            v-tooltip="'Enter your username'"
                        />
                        <label :for="field.name">
                            {{ $t(field.label as string) }}
                        </label>
                    </FloatLabel>
                    <ToggleButton
                        v-else-if="field.type === 'toggle'"
                        :id="field.name"
                        v-model="checked"
                        class="w-full"
                        onIcon="pi pi-check"
                        offIcon="pi pi-times"
                        onLabel="On"
                        offLabel="Off"
                    />
                    <Table
                        v-else-if="field.type === 'table'"
                        :component="field.component"
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
