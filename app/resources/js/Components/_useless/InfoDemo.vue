<template>
    <div>
        <p>
            There are <strong>{{ totalProducts }}</strong> products in total in
            this list.
        </p>
        <div class="flex justify-content-end">
            <Button type="button" label="Close" @click="closeDialog"></Button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, inject } from "vue";

const totalProducts = ref(0);
const dialogRef = inject("dialogRef");

onMounted(() => {
    totalProducts.value = dialogRef.value.data.totalProducts;
});

const closeDialog = () => {
    dialogRef.value.close();
};
</script>
