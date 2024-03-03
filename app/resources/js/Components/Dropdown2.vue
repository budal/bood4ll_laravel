<script setup lang="ts">
import { isValidUrl, getData } from "@/helpers";
import { ref } from "vue";

const props = withDefaults(
    defineProps<{
        id?: string;
        value?: any;
        component?: any;
        url?: string | { route: string; attributes: string[] };
        urlAttributes?: any;
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

const routeUrl = {
    route: props.url,
    attributes: props.urlAttributes,
};

if (typeof props.url === "object") {
    routeUrl.attributes.push(props.urlAttributes);
}

if (props.url) {
    loading.value = true;
    getData(isValidUrl(routeUrl as any)).then((content) => {
        dropdownItems.value = content;
        loading.value = false;
    });
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
