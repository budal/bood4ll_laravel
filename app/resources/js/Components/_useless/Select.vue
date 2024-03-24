<script setup lang="ts">
import {
    ComboboxAnchor,
    ComboboxCancel,
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
    TagsInputClear,
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
        disabled?: boolean;
        selected?: any;
        disableSearch?: boolean;
        required?: boolean;
        autocomplete?: boolean;
        multiple?: boolean;
        placeholder?: string;
    }>(),
    {
        disabled: false,
        disableSearch: false,
        multiple: false,
        placeholder: "",
    },
);

const emit = defineEmits(["update:modelValue"]);

const searchTerm = ref("");

const content = props.content;

// content.unshift({ id: 20, name: "[ root ]" });

const filteredContent = computed(() =>
    searchTerm.value === ""
        ? content
        : content.filter((item: { key: number; name: string }) => {
              return item.name
                  .toLowerCase()
                  .includes(searchTerm.value.toLowerCase());
          }),
);

const selectedItemsIds =
    props.modelValue.length > 0
        ? props.modelValue.map((item: any) => item.id)
        : [];

const selectedContent = content.filter((item: any) => {
    if (props.multiple) {
        return selectedItemsIds.includes(item.id);
    } else {
        return props.modelValue === item?.id;
    }
});

const selectedItems = ref(
    props.multiple ? selectedContent : selectedContent[0],
);

const onOpen = () => {
    emit(
        "update:modelValue",
        props.multiple ? selectedItems : selectedItems.value?.id,
    );
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
        aria-readonly="true"
        v-model="selectedItems"
        v-model:search-term="searchTerm"
        :multiple="multiple"
        @update:open="onOpen"
        :disabled="disabled"
        class="my-4 mx-auto relative"
    >
        <ComboboxAnchor
            class="w-full min-h-[41px] inline-flex items-center justify-between leading-none gap-[5px] bg-zero-white dark:bg-zero-black border border-zero-light dark:border-zero-dark rounded-md focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-light dark:focus-within:ring-primary-dark focus-within:ring-offset-1 focus-within:ring-offset-primary-light dark:focus-within:ring-offset-primary-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus-within:shadow-[0_0_0_2px] focus-within:shadow-primary-light dark:focus-within:shadow-primary-dark data-[placeholder]:text-zero-light/50 transition ease-in-out duration-500 disabled:opacity-25"
        >
            <div
                v-if="multiple === false"
                class="flex flex-wrap gap-1 items-center my-[6px] ml-2 w-full"
            >
                <span
                    class="flex text-zero-light dark:text-zero-dark bg-zero-white dark:bg-zero-black cursor-default"
                >
                    {{ $t(selectedItems?.name || "") }}
                </span>
                <div class="grow w-0">
                    <ComboboxInput
                        :id="id"
                        :name="name"
                        :placeholder="placeholder"
                        :autocomplete="autocomplete ? name : 'off'"
                        :required="
                            false
                            // required && selectedContent.length === 0
                        "
                        class="p-0 w-full bg-transparent text-ellipsis border-0 outline-0 focus:ring-0 placeholder:text-sm placeholder-primary-dark/20 dark:placeholder-primary-dark/20 text-zero-light dark:text-zero-dark"
                    />
                </div>
            </div>
            <template v-else>
                <TagsInputRoot
                    v-slot="{ values: selectedItems }"
                    :model-value="selectedItems"
                    :disabled="disabled"
                    @update:modelValue="onOpen"
                    class="flex flex-wrap gap-1 items-center my-[6px] ml-2 w-full"
                >
                    <TagsInputItem
                        v-for="(item, key) in selectedItems"
                        :key="key"
                        :value="item"
                        :disabled="
                            // @ts-expect-error
                            item.disabled
                        "
                        class="items-center p-1 flex justify-center gap-2 data-[state=active]:text-info-light data-[state=active]:dark:text-info-dark data-[state=active]:bg-info-light data-[state=active]:dark:bg-info-dark data-[state=active]:border-info-light data-[state=active]:dark:border-info-dark data-[state=inactive]:text-zero-light data-[state=inactive]:dark:text-zero-dark data-[state=inactive]:bg-zero-light data-[state=inactive]:dark:bg-zero-dark data-[state=inactive]:border-zero-light data-[state=inactive]:dark:border-zero-dark data-[disabled]:text-zero-light/50 data-[disabled]:dark:text-zero-dark/50 data-[disabled]:bg-zero-light/50 data-[disabled]:dark:bg-zero-dark/50 data-[disabled]:border-zero-light/50 data-[disabled]:dark:border-zero-dark/50 rounded-md placeholder:text-xs sm:placeholder:text-sm text-xs sm:text-sm ring-0 border"
                    >
                        <TagsInputItemText>
                            {{
                                // @ts-expect-error
                                item.name
                            }}</TagsInputItemText
                        >
                        <TagsInputItemDelete
                            v-if="multiple == true"
                            class="cursor-pointer data-[disabled]:pointer-events-none"
                        >
                            <Icon
                                icon="mdi:close-circle-outline"
                                class="w-4 h-4 text-zero-light dark:text-zero-dark"
                            />
                        </TagsInputItemDelete>
                    </TagsInputItem>

                    <div class="grow w-0">
                        <ComboboxInput as-child>
                            <TagsInputInput
                                :id="id"
                                :name="name"
                                :placeholder="placeholder"
                                :autocomplete="autocomplete ? name : 'off'"
                                :required="
                                    required && selectedContent.length === 0
                                "
                                class="p-0 w-full bg-transparent text-ellipsis border-0 outline-0 focus:ring-0 placeholder:text-sm placeholder-primary-dark/20 dark:placeholder-primary-dark/20 text-zero-light dark:text-zero-dark"
                                @keydown.enter.prevent
                            />
                        </ComboboxInput>
                    </div>
                </TagsInputRoot>
            </template>

            <ComboboxTrigger class="px-2">
                <Icon
                    icon="mdi:chevron-down"
                    class="w-6 h-6 text-zero-light dark:text-zero-dark"
                />
            </ComboboxTrigger>
        </ComboboxAnchor>
        <ComboboxContent
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
                            {{ $t(item.name) }}
                        </span>
                    </ComboboxItem>
                </ComboboxGroup>
            </ComboboxViewport>
        </ComboboxContent>
    </ComboboxRoot>
</template>
