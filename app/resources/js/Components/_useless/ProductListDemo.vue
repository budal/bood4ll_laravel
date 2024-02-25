<template>
    <div>
        <div class="flex justify-content-end mt-1 mb-3">
            <Button
                icon="pi pi-external-link"
                label="Nested Dialog"
                outlined
                severity="success"
                @click="showInfo"
            />
        </div>
        <DataTable :value="products">
            <Column field="code" header="Code"></Column>
            <Column field="name" header="Name"></Column>
            <Column header="Image">
                <template #body="slotProps">
                    <img
                        :src="
                            'https://primefaces.org/cdn/primevue/images/product/' +
                            slotProps.data.image
                        "
                        :alt="slotProps.data.name"
                        class="w-4rem"
                    />
                </template>
            </Column>
            <Column field="category" header="Category"></Column>
            <Column field="quantity" header="Quantity"></Column>
            <Column style="width: 5rem">
                <template #body="slotProps">
                    <Button
                        type="button"
                        icon="pi pi-plus"
                        text
                        rounded
                        @click="selectProduct(slotProps.data)"
                    ></Button>
                </template>
            </Column>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, inject } from "vue";
import { useDialog } from "primevue/usedialog";
import { ProductService } from "@/service/ProductService";
import InfoDemo from "@/Components/_useless/InfoDemo.vue";

const dialogRef = inject("dialogRef");
const dialog = useDialog();
const products = ref(null);

onMounted(() => {
    ProductService.getProductsSmall().then(
        (data) => (products.value = data.slice(0, 5)),
    );
});

const selectProduct = (data) => {
    dialogRef.value.close(data);
};

const showInfo = () => {
    dialog.open(InfoDemo, {
        props: {
            header: "Information",
            modal: true,
            dismissableMask: true,
        },
        data: {
            totalProducts: products.value ? products.value.length : 0,
        },
    });
};
</script>
