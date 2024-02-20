<script setup lang="ts">
import Button from "@/Components/Button.vue";
import Modal from "@/Components/Modal.vue";
import { isValidUrl, toast } from "@/helpers";
import { Icon } from "@iconify/vue";
import { router, useForm } from "@inertiajs/vue3";
import { transChoice } from "laravel-vue-i18n";
import { ref } from "vue";

const props = withDefaults(
    defineProps<{
        id?: string;
        name?: string;
        modelValue?: any;
        disabled?: boolean;
        class?: string;
        type?: "button" | "submit" | "reset";
        color?:
            | "zero"
            | "primary"
            | "secondary"
            | "danger"
            | "success"
            | "warning"
            | "info";
        padding?: number | string;
        rounded?:
            | "rounded-none"
            | "rounded-sm"
            | "rounded"
            | "rounded-md"
            | "rounded-lg"
            | "rounded-xl"
            | "rounded-2xl"
            | "rounded-3xl"
            | "rounded-full"
            | "rounded-s-none"
            | "rounded-s-sm"
            | "rounded-s"
            | "rounded-s-md"
            | "rounded-s-lg"
            | "rounded-s-xl"
            | "rounded-s-2xl"
            | "rounded-s-3xl"
            | "rounded-s-full"
            | "rounded-e-none"
            | "rounded-e-sm"
            | "rounded-e"
            | "rounded-e-md"
            | "rounded-e-lg"
            | "rounded-e-xl"
            | "rounded-e-2xl"
            | "rounded-e-3xl"
            | "rounded-e-full"
            | "rounded-t-none"
            | "rounded-t-sm"
            | "rounded-t"
            | "rounded-t-md"
            | "rounded-t-lg"
            | "rounded-t-xl"
            | "rounded-t-2xl"
            | "rounded-t-3xl"
            | "rounded-t-full"
            | "rounded-r-none"
            | "rounded-r-sm"
            | "rounded-r"
            | "rounded-r-md"
            | "rounded-r-lg"
            | "rounded-r-xl"
            | "rounded-r-2xl"
            | "rounded-r-3xl"
            | "rounded-r-full"
            | "rounded-b-none"
            | "rounded-b-sm"
            | "rounded-b"
            | "rounded-b-md"
            | "rounded-b-lg"
            | "rounded-b-xl"
            | "rounded-b-2xl"
            | "rounded-b-3xl"
            | "rounded-b-full"
            | "rounded-l-none"
            | "rounded-l-sm"
            | "rounded-l"
            | "rounded-l-md"
            | "rounded-l-lg"
            | "rounded-l-xl"
            | "rounded-l-2xl"
            | "rounded-l-3xl"
            | "rounded-l-full"
            | "rounded-ss-none"
            | "rounded-ss-sm"
            | "rounded-ss"
            | "rounded-ss-md"
            | "rounded-ss-lg"
            | "rounded-ss-xl"
            | "rounded-ss-2xl"
            | "rounded-ss-3xl"
            | "rounded-ss-full"
            | "rounded-se-none"
            | "rounded-se-sm"
            | "rounded-se"
            | "rounded-se-md"
            | "rounded-se-lg"
            | "rounded-se-xl"
            | "rounded-se-2xl"
            | "rounded-se-3xl"
            | "rounded-se-full"
            | "rounded-ee-none"
            | "rounded-ee-sm"
            | "rounded-ee"
            | "rounded-ee-md"
            | "rounded-ee-lg"
            | "rounded-ee-xl"
            | "rounded-ee-2xl"
            | "rounded-ee-3xl"
            | "rounded-ee-full"
            | "rounded-es-none"
            | "rounded-es-sm"
            | "rounded-es"
            | "rounded-es-md"
            | "rounded-es-lg"
            | "rounded-es-xl"
            | "rounded-es-2xl"
            | "rounded-es-3xl"
            | "rounded-es-full"
            | "rounded-tl-none"
            | "rounded-tl-sm"
            | "rounded-tl"
            | "rounded-tl-md"
            | "rounded-tl-lg"
            | "rounded-tl-xl"
            | "rounded-tl-2xl"
            | "rounded-tl-3xl"
            | "rounded-tl-full"
            | "rounded-tr-none"
            | "rounded-tr-sm"
            | "rounded-tr"
            | "rounded-tr-md"
            | "rounded-tr-lg"
            | "rounded-tr-xl"
            | "rounded-tr-2xl"
            | "rounded-tr-3xl"
            | "rounded-tr-full"
            | "rounded-br-none"
            | "rounded-br-sm"
            | "rounded-br"
            | "rounded-br-md"
            | "rounded-br-lg"
            | "rounded-br-xl"
            | "rounded-br-2xl"
            | "rounded-br-3xl"
            | "rounded-br-full"
            | "rounded-bl-none"
            | "rounded-bl-sm"
            | "rounded-bl"
            | "rounded-bl-md"
            | "rounded-bl-lg"
            | "rounded-bl-xl"
            | "rounded-bl-2xl"
            | "rounded-bl-3xl"
            | "rounded-bl-full";
        textSize?:
            | "text-xs"
            | "text-sm"
            | "text-base"
            | "text-lg"
            | "text-xl"
            | "text-2xl"
            | "text-3xl"
            | "text-4xl"
            | "text-5xl"
            | "text-6xl"
            | "text-7xl"
            | "text-8xl"
            | "text-9xl";
        transform?:
            | "uppercase"
            | "lowercase"
            | "lowercase"
            | "capitalize"
            | "capitalize"
            | "normal-case";
        startIcon?: string;
        endIcon?: string;
        preserveState?: boolean;
        preserveScroll?: boolean;
        method?: "get" | "post" | "patch" | "put" | "delete";
        srOnly?: string;
        title?: string;
        menu?: object;
        link?:
            | {
                  route: string | [];
                  attributes: [];
              }
            | string;
        modal: {
            theme: string;
            title: string;
            subTitle: string;
            buttonTitle?: string;
            buttonIcon?: string;
            buttonTheme?: string;
        };
    }>(),
    {
        type: "submit",
        color: "primary",
        padding: 4,
        rounded: "rounded-md",
        textSize: "text-xs",
        transform: "uppercase",
        preserveState: false,
        preserveScroll: false,
        method: "get",
    },
);

const confirmingDeletionModal = ref(false);

const onClick = () => {
    if (props.link) {
        let indexRoute = new URL(window.location.href);
        const __tab = indexRoute.searchParams.get("__tab");

        const route = new URL(isValidUrl(props.link));
        __tab ? route.searchParams.set("__tab", __tab) : false;

        confirmingDeletionModal.value = true;

        // router.visit(route, {
        //     method: props.method,
        //     preserveState: props.preserveState,
        //     preserveScroll: props.preserveScroll,
        //     onSuccess: () => toast(),
        // });
    }
};

// modal
const closeModal = () => (confirmingDeletionModal.value = false);

// const modalForm = useForm({ list: [] });

// const submitModal = () => {
//     // modalForm.submit(props.method, isValidUrl(modalInfo.value.route), {
//     //     preserveScroll: true,
//     //     preserveState: modalInfo.value.preserveState,
//     //     onSuccess: () => {
//     //         toast();
//     //         closeModal();
//     //     },
//     //     onError: () => {
//     //         toast();
//     //         closeModal();
//     //     },
//     // });
// };

console.log(props.modal.title);
</script>

<template>
    <Modal
        :open="confirmingDeletionModal"
        :title="$t(modal.title)"
        :subTitle="$t(modal.subTitle)"
        :theme="'secondary'"
        @close="closeModal"
    >
        <template #buttons>
            <Button
                color="secondary"
                @click="closeModal"
                start-icon="mdi:cancel-outline"
            >
                {{ $t("Cancel") }}
            </Button>
        </template>
    </Modal>

    <Button color="secondary" @click="onClick" start-icon="mdi:cancel-outline">
        {{ $t("Cancel") }}
    </Button>
</template>
