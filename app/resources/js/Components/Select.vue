<script setup lang="ts">
  import { ref } from 'vue'
  import { ComboboxAnchor, ComboboxContent, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxLabel, ComboboxRoot, ComboboxSeparator, ComboboxTrigger, ComboboxViewport } from 'radix-vue'
  import { Icon } from '@iconify/vue'
  import { computed } from 'vue';

  const props = withDefaults(
    defineProps<{
      id?: string;
      name?: string;
      modelValue: Object | Object[] | string | string[];
      content: any;
      required?: boolean;
      multiple?: boolean;
    }>(),
    {
      multiple: false,
    }
  );

  const selectedContent = ref(props.modelValue)

  const searchTerm = ref('')

  const filteredContent = computed(() =>
    searchTerm.value === ''
      ? props.content
      : props.content.filter((items: {id: string, title: string}) => {
        return items.title.toLowerCase().includes(searchTerm.value.toLowerCase())
      })
  )
</script>

<template>
  <ComboboxRoot 
    class="relative" 
    v-model="selectedContent" 
    v-model:searchTerm="searchTerm"
    :multiple="multiple"
  >
    <ComboboxAnchor class="w-full inline-flex">
      <ComboboxTrigger class="w-full">
        <ComboboxInput
          :id="id" 
          :name="name"
          :placeholder="multiple ? $t('Select one or more options') : $t('Select an option')" 
          class="w-full block p-2 placeholder:text-sm placeholder-primary-dark/20 dark:placeholder-primary-dark/20 bg-zero-light dark:bg-zero-dark text-zero-light dark:text-zero-dark rounded-md border border-zero-light dark:border-zero-dark rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark cursor-pointer transition ease-in-out duration-500 disabled:opacity-25 " 
        />
        <div class="flex absolute inset-y-2 ">
          <div v-for="item in selectedContent" class="flex ml-2 items-center text-primary-light dark:text-primary-dark px-2 rounded-md text-sm p-1 bg-primary-light dark:bg-primary-dark ring-0">
            {{ item }}
            <Icon @click="" icon="mdi:window-close" class="w-4 h-4 ml-1 text-primary-light dark:text-primary-dark" />
          </div>
        </div>
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
          <Icon icon="mdi:chevron-down" class="w-6 h-6 text-primary-light dark:text-primary-dark" />
        </div>
      </ComboboxTrigger>
    </ComboboxAnchor>
    <ComboboxContent class="absolute w-full mt-1 min-w-[160px] bg-white overflow-hidden bg-zero-light dark:bg-zero-dark text-zero-light dark:text-zero-dark rounded-md border border-zero-light dark:border-zero-dark rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark">
      <ComboboxViewport class="p-[5px] max-h-60">
        <ComboboxEmpty class="text-xs font-medium text-center py-2" />
        <ComboboxGroup>
          <ComboboxLabel as="span" class="px-[25px] text-xs leading-[25px] text-mauve11">
            Fruits
          </ComboboxLabel>
          <ComboboxItem
            v-for="item in filteredContent" 
            :key="item.id"
            :value="item.id"
            :disabled="item.disabled"
            class="text-sm p-3 leading-none pr-[35px] pl-[25px] relative select-none data-[disabled]:opacity-25 data-[disabled]:pointer-events-none data-[highlighted]:outline-none data-[highlighted]:bg-zero-light-hover dark:data-[highlighted]:bg-zero-dark-hover cursor-pointer"
          >
            <ComboboxItemIndicator class="absolute left-0 w-[25px] inline-flex items-center justify-center">
              <Icon icon="radix-icons:check" />
            </ComboboxItemIndicator>
            <span>
              {{ $t(item.title) }}
            </span>
          </ComboboxItem>
        </ComboboxGroup>
      </ComboboxViewport>
    </ComboboxContent>
  </ComboboxRoot>
</template>