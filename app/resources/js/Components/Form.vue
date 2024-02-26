<script setup lang="ts">
import { isValidUrl, toast } from "@/helpers";
import { nextTick, ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";

import { inject, onMounted } from "vue";

import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";

const dialogRef = inject("dialogRef");

const props = withDefaults(
    defineProps<{
        create?: any;
        form?: any;
        routes?: any;
        data?: any;
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

let formItems: FormItems = {};

// props.form.forEach((forms: any) => {
//     forms.fields.forEach((fields: any) => {
//         fields.forEach((field: any) => {
//             formItems[field.name] = props.data
//                 ? props?.data[field.name] || ""
//                 : "";
//         });
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

let tab = ref("");

const changeTab = (item: any) => {
    tab.value = item || null;
};

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

let tabs = ref(dialogRef.value.data);

const value = ref(null);
</script>

<template>
    <div
        v-if="status"
        :class="`mb-4 rounded-md font-medium bg-${statusTheme}-light dark:bg-${statusTheme}-dark text-sm text-center text-${statusTheme}-light dark:text-${statusTheme}-dark`"
    >
        {{ $t(status) }}
    </div>

    <TabView>
        <TabPanel v-for="tab in tabs" :header="$t(tab.label)">
            <form
                v-if="tab.showIf !== false"
                @submit.prevent="sendForm(tab.id)"
                class="space-y-6"
            >
                <div :class="`grid sm:grid-cols-${tab.cols} sm:gap-2`">
                    <div
                        v-for="field in tab.fields"
                        :class="`pt-6 ${
                            field.span ? `sm:col-span-${field.span}` : ''
                        }`"
                    >
                        <div class="flex justify-content-center">
                            <FloatLabel class="w-full">
                                <InputText
                                    id="username"
                                    v-model="value"
                                    class="w-full"
                                    v-tooltip="'Enter your username'"
                                />
                                <label for="username">{{ field.label }}</label>
                            </FloatLabel>
                        </div>
                    </div>
                </div>

                <!-- <div
                    v-if="props.routes[tab.id]"
                    class="flex gap-4"
                    :class="
                        props.routes[tab.id].buttonClass || 'justify-start'
                    "
                ></div> -->
            </form>
        </TabPanel>
    </TabView>
</template>
