<script setup lang="ts">
import { Ref, inject, ref } from "vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import { fetchData } from "@/helpers";

const confirm = useConfirm();
const toast = useToast();
const dialogRef: Ref<any> | undefined = inject("dialogRef");

const data = ref(dialogRef?.value.data.action);

const close = (event: Event) => {
    if (dialogRef?.value) {
        dialogRef.value.close(event);
    }
};

// "info" | "secondary" | "success" | "contrast" | "warn" | "error" | "undefined"

const send = (event: Event) => {
    // fetchData()

    console.log(1);
    if (data.value) {
        toast.add({
            severity: data.value.toastClass || "info",
            summary: trans(data.value.toastTitle || "Confirmed"),
            detail: trans(data.value.toast || null),
            life: 3000,
        });
    }
    close(event);
};

const handleConfirm = (event: Event) => {
    if (data.value?.visible != false) {
        if (data.value?.confirm === true) {
            confirmDialog(event);
        } else {
            send(event);
        }
    } else {
        toast.add({
            severity: "warn",
            summary: trans("Error"),
            detail: trans("You cant do this action."),
            life: 3000,
        });
        close(event);
    }
};

const confirmDialog = (event: Event) => {
    if (data.value) {
        confirm.require({
            group: "popup",
            target:
                event.currentTarget instanceof HTMLElement
                    ? event.currentTarget
                    : undefined,
            message: trans(
                data.value.popup || "Do you want confirm this action?",
            ),
            icon: data.value.popupIcon || "pi pi-info-circle",
            rejectClass:
                data.value.popupCancelClass ||
                "p-button-secondary p-button-outlined p-button-sm",
            acceptClass:
                data.value.popupConfirmClass || "p-button-info p-button-sm",
            rejectLabel: trans(data.value.popupCancel || "No"),
            acceptLabel: trans(data.value.popupConfirm || "Yes"),
            accept: () => send(event),
        });
    }
};

const dialogCancelLabel = trans(data.value?.dialogCancel || "Cancel");
const dialogCancelIcon = data.value?.dialogCancelIcon || "pi pi-times";
const dialogConfirmLabel = trans(data.value?.dialogConfirm || "Confirm");
const dialogConfirmClass = data.value?.dialogConfirmClass || "info";
const dialogConfirmIcon = data.value?.dialogConfirmIcon || "pi pi-check";
</script>

<template>
    <div
        v-if="data.visible != false"
        class="pt-2 flex justify-content-end gap-2"
    >
        <Button
            type="button"
            :label="dialogCancelLabel"
            severity="secondary"
            :icon="dialogCancelIcon"
            @click="close"
            autofocus
        />
        <Button
            type="button"
            :label="dialogConfirmLabel"
            :severity="dialogConfirmClass"
            :icon="dialogConfirmIcon"
            @click="handleConfirm"
        />
    </div>
</template>
