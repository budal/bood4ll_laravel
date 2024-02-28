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
        component?: any;
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

console.log(props.formRoute);

const onDataLoad = () => {
    if (props.data?.id) {
        getData("http://localhost/apps/units/edit/" + props.data?.id).then(
            (content) => {
                // console.log(content);
                //     contentItems.value = content.data;
                //     nextPageURL.value = content.next_page_url;
                //     loadingTable.value = false;
            },
        );
    }
};
</script>

<template>
    <form
        v-if="component.showIf !== false"
        @submit.prevent="sendForm(component.id)"
        class="space-y-6"
    >
        <div :class="`grid sm:grid-cols-${component.cols} sm:gap-2`">
            <div
                v-for="field in component.fields"
                :class="`pt-6 ${field.span ? `sm:col-span-${field.span}` : ''}`"
            >
                <div class="flex justify-content-center">
                    <FloatLabel v-if="field.type === 'input'" class="w-full">
                        <InputText
                            :id="field.name"
                            v-model="value"
                            class="w-full"
                            v-tooltip="'Enter your username'"
                        />
                        <label :for="field.name">
                            {{ $t(field.label || "") }}</label
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
                            {{ $t(field.label || "") }}</label
                        >
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
                    <span v-else class="text-xs">
                        {{ field.name }}
                    </span>
                </div>
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
</template>
