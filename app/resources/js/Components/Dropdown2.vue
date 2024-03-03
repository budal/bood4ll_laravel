<script setup lang="ts">
import { isValidUrl, getData } from "@/helpers";
import { ref } from "vue";

const props = withDefaults(
    defineProps<{
        id?: string;
        value?: any;
        component?: any;
        url?: string | { route: string; attributes: string[] };
        optionValue?: string;
        optionLabel?: string;
        placeholder?: string;
        itemSize?: number;
    }>(),

    {
        optionValue: "id",
        optionLabel: "name",
        placeholder: "Select an item",
        itemSize: 40,
    },
);

const loading = ref(false);
const dropdownItems = ref([]);

if (props.url) {
    loading.value = true;
    getData(isValidUrl(props.url)).then((content) => {
        dropdownItems.value = content;
        loading.value = false;
    });
    //     Array.from(formRef).find((item) => item.id === id);
    //     Array.from(formRef).some((item) => item.id === id) === false
}

const value = ref(props.value);
</script>

<template>
    <Dropdown
        :id="id"
        v-model="value"
        :options="dropdownItems"
        :optionValue="optionValue"
        :optionLabel="optionLabel"
        :placeholder="$t(placeholder)"
        :virtualScrollerOptions="{ itemSize: props.itemSize }"
        showClear
        checkmark
        filter
        :loading="loading"
        :highlightOnSelect="true"
        class="w-full"
    />
</template>
