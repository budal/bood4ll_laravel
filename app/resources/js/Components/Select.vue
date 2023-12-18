<script setup lang="ts">
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxLabel,
    ComboboxRoot,
    ComboboxTrigger,
    ComboboxViewport,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
    TagsInputRoot,
} from "radix-vue";
import { Icon } from "@iconify/vue";
import { ref, watch, computed } from "vue";

const props = withDefaults(
    defineProps<{
        id?: string;
        name?: string;
        modelValue?: any;
        content: any;
        selected?: any;
        disableSearch?: boolean;
        required?: boolean;
        autocomplete?: boolean;
        multiple?: boolean;
        placeholder?: string;
    }>(),
    {
        disableSearch: false,
        multiple: false,
        placeholder: '',
    },
);

const emit = defineEmits(["update:modelValue"]);

const searchTerm = ref("");

const filteredContent = computed(() =>
    searchTerm.value === ""
        ? props.content
        : props.content.filter((item: { key: number; name: string }) => {
              return item.name
                  .toLowerCase()
                  .includes(searchTerm.value.toLowerCase());
          }),
);

const selectedIds =
    props.modelValue.length > 0
        ? props.modelValue.map((item: any) => item.id)
        : [];

const selectedContent = props.content.filter((item: any) => {
    return selectedIds.includes(item.id);
});

const selectedItems = ref(selectedContent);

const onEscape = () => {
    searchTerm.value = "";
};

const onOpen = () => {
    onEscape();
    emit("update:modelValue", selectedItems);
};

watch(
    selectedContent,
    () => {
        searchTerm.value = "";
    },
    { deep: true },
);
</script>

<template>
    <ComboboxRoot
        v-model="selectedItems"
        v-model:search-term="searchTerm"
        :multiple="multiple"
        @update:open="onOpen"
        class="my-4 mx-auto relative"
    >
        <ComboboxAnchor
            class="w-full min-h-[41px] inline-flex items-center justify-between text-[13px] leading-none gap-[5px] bg-zero-white dark:bg-zero-black border border-zero-light dark:border-zero-dark rounded-md focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-light dark:focus-within:ring-primary-dark focus-within:ring-offset-1 focus-within:ring-offset-primary-light dark:focus-within:ring-offset-primary-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus-within:shadow-[0_0_0_2px] focus-within:shadow-primary-light dark:focus-within:shadow-primary-dark data-[placeholder]:text-zero-light/50 transition ease-in-out duration-500 disabled:opacity-25"
        >
            <TagsInputRoot
                v-slot="{ values: selectedItems }"
                :model-value="selectedItems"
                delimiter=","
                @update:modelValue="onOpen"
                class="flex flex-wrap gap-1 items-center my-[6px] ml-2 w-full"
            >
                <TagsInputItem
                    v-for="item in selectedItems"
                    :key="item"
                    :value="item"
                    class="p-1 flex items-center justify-center gap-2 text-zero-light dark:text-zero-dark rounded-md placeholder:text-xs sm:placeholder:text-sm text-xs sm:text-sm aria-[current=true]:bg-grass9 bg-zero-light dark:bg-zero-dark ring-0 border border-zero-light dark:border-zero-dark"
                >
                    <TagsInputItemText class="text-sm">{{
                        // @ts-expect-error
                        item.name
                    }}</TagsInputItemText>
                    <TagsInputItemDelete>
                        <Icon
                            icon="mdi:close-circle-outline"
                            class="w-4 h-4 text-zero-light dark:text-zero-dark cursor-pointer"
                        />
                    </TagsInputItemDelete>
                </TagsInputItem>

                <ComboboxTrigger class="grow w-0">
                    <ComboboxInput as-child>
                        <TagsInputInput
                            :id="id"
                            :name="name"
                            :placeholder="placeholder"
                            :autocomplete="autocomplete ? name : 'off'"
                            :required="required && selectedContent.length === 0"
                            class="p-0 w-full bg-transparent text-ellipsis border-0 outline-0 focus:ring-0 placeholder:text-sm placeholder-primary-dark/20 dark:placeholder-primary-dark/20 text-zero-light dark:text-zero-dark"
                            @keydown.enter.prevent
                        />
                    </ComboboxInput>
                </ComboboxTrigger>
            </TagsInputRoot>

            <ComboboxTrigger>
                <Icon
                    icon="mdi:chevron-down"
                    class="w-6 h-6 text-zero-light dark:text-zero-dark"
                />
            </ComboboxTrigger>
        </ComboboxAnchor>
        <ComboboxContent
            @escapeKeyDown="onEscape"
            @pointerDownOutside="onEscape"
            @closeAutoFocus="onEscape"
            class="absolute z-[4] w-full mt-1 min-w-[160px] overflow-hidden bg-zero-white dark:bg-zero-black text-zero-light dark:text-zero-dark rounded-md border border-zero-light dark:border-zero-dark focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark shadow-[0px_10px_38px_-10px_rgba(22,_23,_24,_0.35),_0px_10px_20px_-15px_rgba(22,_23,_24,_0.2)] will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
        >
            <ComboboxViewport class="p-[5px] max-h-60">
                <ComboboxEmpty class="text-xs font-medium text-center">{{
                    $t("No items to show.")
                }}</ComboboxEmpty>

                <ComboboxGroup>
                    <ComboboxItem
                        v-for="item in filteredContent"
                        :key="item.id"
                        :value="item"
                        :disabled="item.disabled"
                        class="text-sm p-3 leading-none pr-[35px] pl-[25px] relative select-none data-[disabled]:opacity-25 data-[disabled]:pointer-events-none data-[highlighted]:outline-none data-[highlighted]:bg-zero-light-hover dark:data-[highlighted]:bg-zero-dark-hover cursor-pointer"
                    >
                        <ComboboxItemIndicator
                            class="absolute left-0 w-[25px] inline-flex items-center justify-center"
                        >
                            <Icon icon="radix-icons:check" />
                        </ComboboxItemIndicator>
                        <span>
                            {{ item.name }}
                        </span>
                    </ComboboxItem>
                </ComboboxGroup>
            </ComboboxViewport>
        </ComboboxContent>
    </ComboboxRoot>
</template>
