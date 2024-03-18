<script setup lang="ts">
import { fetchData } from "@/helpers";
import { trans, transChoice } from "laravel-vue-i18n";
import { ReplacementsInterface } from "laravel-vue-i18n/interfaces/replacements";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";

const props = withDefaults(
    defineProps<{
        url?: any;
        id?: string | number;
        checked?: boolean;
    }>(),
    {
        checked: false,
    },
);

const toast = useToast();

const checked = ref(
    props.checked === true
        ? {
              icon: "pi pi-check",
              severity: "success",
          }
        : {
              icon: "pi pi-times",
              severity: "danger",
          },
);

const emit = defineEmits(["click"]);

const loading = ref(false);

const loadingState = () => {
    fetchData(props.url, {
        method: "post",
        data: { list: [props.id] },
        complement: {
            mode: "toggle",
        },
        onBefore: () => {
            loading.value = true;
        },
        onSuccess: (success: {
            message: string;
            deactivate: boolean;
            length: number;
            replacements: ReplacementsInterface;
        }) => {
            if (success.deactivate === true) {
                checked.value = {
                    icon: "pi pi-times",
                    severity: "danger",
                };
            } else {
                checked.value = {
                    icon: "pi pi-check",
                    severity: "success",
                };
            }

            toast.add({
                severity: "success",
                summary: trans("Success"),
                detail: transChoice(
                    success.message,
                    success.length,
                    success.replacements,
                ),
                life: 3000,
            });
        },
        onFinish: () => {
            loading.value = false;
        },
        onError: (error: { message: string; length: number }) => {
            toast.add({
                severity: "error",
                summary: trans("Error"),
                detail: transChoice(error.message, error.length),
                life: 3000,
            });
        },
    });
};
</script>

<template>
    <Button
        rounded
        :icon="checked.icon"
        :severity="checked.severity"
        :loading="loading"
        @click="
            loadingState();
            emit('click');
        "
    >
    </Button>
</template>
