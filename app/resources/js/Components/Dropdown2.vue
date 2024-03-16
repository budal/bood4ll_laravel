<script setup lang="ts">
import { fetchData } from "@/helpers";
import { trans } from "laravel-vue-i18n";
import { onMounted, ref } from "vue";

const props = withDefaults(
    defineProps<{
        id?: string;
        value?: any;
        multiple?: boolean;
        component?: any;
        url?:
            | string
            | {
                  route: string;
                  id?: string | number | undefined;
              };
        urlAttributes?: any;
        optionValue?: string;
        optionLabel?: string;
        placeholder?: string;
        itemSize?: number;
    }>(),

    {
        multiple: false,
        optionValue: "id",
        optionLabel: "name",
        placeholder: trans("Select an item"),
        itemSize: 40,
    },
);

const loading = ref(false);
const dropdownItems = ref([]);

onMounted(() => {
    if (props.url?.id !== undefined) {
        // console.log(props.url);

        fetchData(props.url.route, {
            complement: {
                id: props.url.id,
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
    }
});

const value = ref(props.value);
</script>

<template>
    <Dropdown
        v-if="multiple === false"
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
