<script setup lang="ts">
import { fetchData } from "@/helpers";
import { trans } from "laravel-vue-i18n";
import { onMounted, ref } from "vue";

const props = withDefaults(
    defineProps<{
        id?: string | number;
        value?: any;
        multiple?: boolean;
        source?: any;
        optionValue?: string;
        optionLabel?: string;
        placeholder?: string;
        itemSize?: number;
    }>(),

    {
        multiple: false,
        optionValue: "id",
        optionLabel: "name",
        itemSize: 40,
    },
);

const placeholderValue = trans(
    props.placeholder
        ? props.placeholder
        : props.multiple === true
          ? "Select an item or more"
          : "Select an item",
);

const loading = ref(false);
const dropdownItems = ref([]);

onMounted(() => {
    fetchData(props.source, {
        complement: {
            id: props.id,
        },
        onBefore: () => {
            loading.value = true;
        },
        onSuccess: (content: never[]) => {
            dropdownItems.value = content;
        },
        onFinish: () => {
            loading.value = false;
        },
        onError: (error: { message: string }) => {
            console.log(error.message);
        },
    });
});

const value = ref(props.value);

console.log(props.value?.abilities);
</script>

<template>
    <MultiSelect
        v-if="multiple === true"
        :id="id"
        v-model="value"
        display="chip"
        :options="dropdownItems"
        :optionValue="optionValue"
        :optionLabel="optionLabel"
        :placeholder="$t(placeholderValue)"
        :virtualScrollerOptions="{ itemSize: props.itemSize }"
        showClear
        checkmark
        filter
        :loading="loading"
        :highlightOnSelect="true"
        class="w-full"
    >
        <template #empty>
            {{ $t("No items to show.") }}
        </template>
        <template #emptyfilter>
            {{ $t("No items to show.") }}
        </template>
    </MultiSelect>
    <Dropdown
        v-else
        :id="id"
        v-model="value"
        :options="dropdownItems"
        :optionValue="optionValue"
        :optionLabel="optionLabel"
        :placeholder="$t(placeholderValue)"
        :virtualScrollerOptions="{ itemSize: props.itemSize }"
        showClear
        checkmark
        filter
        :loading="loading"
        :highlightOnSelect="true"
        class="w-full"
    >
        <template #empty>
            {{ $t("No items to show.") }}
        </template>
        <template #emptyfilter>
            {{ $t("No items to show.") }}
        </template></Dropdown
    >
</template>
