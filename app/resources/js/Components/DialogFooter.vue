<script setup lang="ts">
import { inject } from "vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

const confirm = useConfirm();
const toast = useToast();
const dialogRef = inject("dialogRef");

const confirm2 = (event: any) => {
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
            dialogRef.value.close(event);
        },
    });
};
</script>

<template>
    <Button
        type="button"
        label="Cancel"
        icon="pi pi-times"
        @click="confirm2($event)"
        autofocus
    ></Button>
</template>
