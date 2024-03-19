<script setup lang="ts">
import { fetchData } from "@/helpers";
import { trans, transChoice } from "laravel-vue-i18n";
import { ReplacementsInterface } from "laravel-vue-i18n/interfaces/replacements";
import { useToast } from "primevue/usetoast";
import { onMounted, ref } from "vue";

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

let checked3 = props.checked;

const checkedRef = ref();

onMounted(() => {
    checkedRef.value =
        props.checked === true
            ? {
                  icon: "pi pi-check",
                  severity: "success",
              }
            : {
                  icon: "pi pi-times",
                  severity: "danger",
              };
});

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
            length: number;
            replacements: ReplacementsInterface;
        }) => {
            if (success.deactivate === true) {
                checkedRef.value = {
                    icon: "pi pi-times",
                    severity: "danger",
                };
            } else {
                checkedRef.value = {
                    icon: "pi pi-check",
                    severity: "success",
                };
            }

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

const checked2 =
    props.checked === true
        ? {
              icon: "pi pi-check",
              severity: "success",
          }
        : {
              icon: "pi pi-times",
              severity: "danger",
          };
</script>

<template>
    {{ props.checked }}
    <Button
        rounded
        :icon="checked2.icon"
        :severity="checked2.severity"
        :loading="loading"
        @click="
            loadingState();
            emit('click');
        "
    >
    </Button>
    {{ checkedRef }}
</template>
