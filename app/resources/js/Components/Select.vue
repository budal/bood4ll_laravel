<script setup lang="ts">
  import { ComboboxAnchor, ComboboxContent, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxLabel, ComboboxRoot, ComboboxSeparator, ComboboxTrigger, ComboboxViewport } from 'radix-vue'
  import { Icon } from '@iconify/vue'
  import { ref, computed } from 'vue';
  import SelectItems from '@/Components/SelectItems.vue'
  
  const props = withDefaults(
    defineProps<{
      id?: string;
      name?: string;
      modelValue?: any;
      content: any;
      disableSearch?: boolean;
      required?: boolean;
      multiple?: boolean;
    }>(),
    {
      disableSearch: false,
      multiple: false,
    }
  );

  const emit = defineEmits(['update:modelValue']);

  const selectedItems = ref(props.modelValue ? props.modelValue : (props.multiple ? [] : null))
  const searchTerm = ref('')
  const showContent = computed(() => props.content.filter(
    (e: any) => { 
      return props.multiple ? selectedItems.value.includes(e.id) : selectedItems.value == e.id; 
    }
  ))

  const filteredItems = computed(() =>
    searchTerm.value === ''
      ? props.content
      : props.content.filter((item: {id: string, name: string}) => {
        const result = item.name.toLowerCase().includes(searchTerm.value.toLowerCase())
        
        return result
      })
  )

  // let resultado = props.content.filter(pai => {
  //   pai.filhos = props.content.filter(filho => filho.parent_id === pai.id);
  //     return pai.parent_id === null
  // });

  // console.log(resultado)
</script>

<template>
  <ComboboxRoot
    v-model="selectedItems" 
    v-model:searchTerm="searchTerm"
    class="relative" 
    :multiple="multiple"
    @update:open="() => emit('update:modelValue', selectedItems)"
  >
    <ComboboxAnchor class="relative w-full min-h-[41px] flex bg-zero-light dark:bg-zero-dark border border-zero-light dark:border-zero-dark rounded-md focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-light dark:focus-within:ring-primary-dark focus-within:ring-offset-1 focus-within:ring-offset-primary-light dark:focus-within:ring-offset-primary-dark transition ease-in-out duration-500 disabled:opacity-25">
      <div class="w-full">
        <div class="flex flex-wrap gap-1 items-center my-[6px] ml-2">
          <div 
            v-for="item in showContent" 
            :key="item.id" 
            class="p-1 flex items-center text-primary-light dark:text-primary-dark rounded-md text-sm bg-primary-light dark:bg-primary-dark ring-0"
            :class= "(disableSearch || !multiple) ? 'text-zero-light dark:text-zero-dark rounded-md text-sm bg-zero-light dark:bg-zero-dark' : ''" 
          >
            {{ $t(item.name) }}
            <button v-if="!disableSearch && multiple" type="button" class="ml-1 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark">
              <Icon 
                icon="mdi:close-circle-outline" 
                class="w-4 h-4 text-primary-light dark:text-primary-dark cursor-pointer" 
                :key="item.id"
              />
            </button>
          </div>
          <ComboboxTrigger class="grow w-0 ">
            <ComboboxInput
              v-if="!disableSearch"
              class="p-0 w-full bg-transparent text-ellipsis border-0 outline-0 focus:ring-0 placeholder:text-sm placeholder-primary-dark/20 dark:placeholder-primary-dark/20 text-zero-light dark:text-zero-dark" 
            />
          </ComboboxTrigger>
        </div>
      </div>
      <ComboboxTrigger>
        <div class="inset-y-0 right-0 mr-2 flex items-center pointer-events-none">
          <button :id="id" :name="name" type="button" class="ml-1 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark">
            <Icon icon="mdi:chevron-down" class="w-6 h-6 text-primary-light dark:text-primary-dark" />
          </button>
        </div>
      </ComboboxTrigger>
    </ComboboxAnchor>

    <ComboboxContent class="absolute z-[4] w-full mt-1 min-w-[160px] bg-white overflow-hidden bg-zero-light dark:bg-zero-dark text-zero-light dark:text-zero-dark rounded-md border border-zero-light dark:border-zero-dark rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark">
      <ComboboxViewport class="p-[5px] max-h-60">
        <ComboboxEmpty class="text-xs font-medium text-center">{{ $t('No items to show.') }}</ComboboxEmpty>
        <SelectItems :items="filteredItems" />
      </ComboboxViewport>
    </ComboboxContent>
  </ComboboxRoot>
</template>