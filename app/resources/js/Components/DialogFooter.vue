<script setup lang="ts">
import { Ref, inject } from "vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

interface DialogRef {
    close: (event?: any) => void;
}

const confirm = useConfirm();
const toast = useToast();
const dialogRef = inject<Ref<DialogRef>>("dialogRef");

const close = (event: any) => {
    if (dialogRef && dialogRef.value) {
        dialogRef.value.close(event);
    }
};

const confirmDialog = (event: any) => {
    confirm.require({
        group: "popup",
        target: event.currentTarget,
        message: "Do you want to delete this record?",
        icon: "pi pi-info-circle",
        rejectClass: "p-button-secondary p-button-outlined p-button-sm",
        acceptClass: "p-button-danger p-button-sm",
        rejectLabel: "Cancel",
        acceptLabel: "Delete",
        accept: () => {
            toast.add({
                severity: "info",
                summary: "Confirmed",
                detail: "Record deleted",
                life: 3000,
            });

            close(event);
        },
    });
};
</script>

<template>
    <div class="flex justify-content-end gap-2">
        <Button
            type="button"
            :label="$t('Cancel')"
            severity="secondary"
            icon="pi pi-times"
            @click="close($event)"
            autofocus
        />
        <Button
            type="button"
            :label="$t('Confirm')"
            icon="pi pi-check"
            @click="confirmDialog($event)"
        />
    </div>
</template>
