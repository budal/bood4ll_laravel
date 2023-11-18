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

  const searchInput = ref('')
  
  const getListId = (data: any) => {
    if (typeof data === 'object' || data instanceof Object)
      return data.map((d: any) => (d.children_recursive) ? [d.id, ...getFlatList(d.children_recursive)] : [d.id]).flat()
    else {
      if (data === '')
        return props.multiple === true ? [] : null
      else 
        return props.multiple === true ? [data] : data
    }
  }
  
  const selectedItems = ref(getListId(props.modelValue))

  emit('update:modelValue', selectedItems)
  
  const getFlatList = (data: any) => data.map((d: any) => (d.children_recursive) ? [d, ...getFlatList(d.children_recursive)] : [d]).flat()

  const flatContent = getFlatList(props.content)
  
  const showContent = computed(() => {
    if (props.multiple === true) {
      return flatContent.filter((x: any) => selectedItems.value.includes(x.id))
    } else {
      return flatContent.filter((x: any) => x.id == selectedItems.value)
    }
  })

  const removeItem = (id: number) => {
    let index = selectedItems.value.indexOf(id);
    selectedItems.value.splice(index, 1);
  }

  function filterArray(arrayList: any, search: string) {
    return arrayList.filter((item: any) => {
      let childrens = item.children_recursive;
      if (childrens && childrens.length){
        item.children_recursive = filterArray(childrens, search);

        if (item.children_recursive && item.children_recursive.length){
          return true;
        }
      }
      return item.name.toLowerCase().indexOf(search) > -1;
    });
  }


  
  const filteredItems = computed(() =>
    searchInput.value == ''
      ? props.content
      : filterArray(props.content, searchInput.value)
  )


  
</script>

<template>
  <ComboboxRoot
    v-model="selectedItems" 
    v-model:searchInput="searchInput"
    class="relative" 
    :multiple="multiple"
    @update:open="() => emit('update:modelValue', selectedItems)"
  >
    <ComboboxAnchor class="relative w-full min-h-[41px] flex bg-zero-light dark:bg-zero-dark border border-zero-light dark:border-zero-dark rounded-md focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-light dark:focus-within:ring-primary-dark focus-within:ring-offset-1 focus-within:ring-offset-primary-light dark:focus-within:ring-offset-primary-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus-within:shadow-[0_0_0_2px] focus-within:shadow-primary-light dark:focus-within:shadow-primary-dark transition ease-in-out duration-500 disabled:opacity-25">
      <div class="w-full">
        <div class="flex flex-wrap gap-1 items-center my-[6px] ml-2">
          <div 
            v-for="item in showContent" 
            :key="item.id" 
            :class= "multiple ? 
              'p-1 flex items-center text-secondary-light dark:text-secondary-dark rounded-md placeholder:text-xs sm:placeholder:text-sm text-xs sm:text-sm bg-secondary-light dark:bg-secondary-dark ring-0 border border-zero-light dark:border-zero-dark' : 
              'items-center text-zero-light dark:text-zero-dark rounded-md placeholder:text-xs sm:placeholder:text-sm bg-zero-light dark:bg-zero-dark'
            " 
          >
            {{ $t(item.name) }}
            <button v-if="multiple" @click="removeItem(item.id)" type="button" class="ml-1 rounded-full focus:outline-none focus:ring-2 focus:ring-secondary-light dark:focus:ring-secondary-dark focus:ring-offset-1 focus:ring-offset-secondary-light dark:focus:ring-offset-secondary-dark">
              <Icon 
                icon="mdi:close-circle-outline" 
                class="w-4 h-4 text-secondary-light dark:text-secondary-dark cursor-pointer" 
                :key="item.id"
              />
            </button>
          </div>
          <ComboboxTrigger class="grow w-0 ">
            <ComboboxInput
              v-if="!disableSearch"
              v-model="searchInput"
              :id="id"
              :name="name"
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