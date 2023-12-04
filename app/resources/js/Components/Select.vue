<script setup lang="ts">
  import SelectItems from '@/Components/SelectItems.vue'
  import { Icon } from '@iconify/vue'
  import { ComboboxAnchor, ComboboxContent, ComboboxEmpty, ComboboxInput, ComboboxRoot, ComboboxTrigger, ComboboxViewport } from 'radix-vue'
  import { Link } from '@inertiajs/vue3'
  import { ref, computed } from 'vue';
  
  const props = withDefaults(
    defineProps<{
      id?: string;
      name?: string;
      modelValue?: any;
      content: any;
      selected?: any;
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
      return data.map((d: any) => (d.children_recursive) 
        ? [d.id, ...getFlatList(d.children_recursive)] 
        : [d.id]).flat()
    else
      if (data === '')
        return props.multiple === true 
          ? [] 
          : null
      else 
        return props.multiple === true 
          ? [data] 
          : data
  }
  
  const selectedItems = ref(getListId(props.modelValue))

  emit('update:modelValue', selectedItems)
  
  const getFlatList = (data: any) => data.map((d: any) => (d.children_recursive) 
    ? [d, ...getFlatList(d.children_recursive)] 
    : [d]).flat()

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

      if (childrens){
        item.children_recursive = filterArray(childrens, search);

        if (item.children_recursive && item.children_recursive.length){
          return true;
        }
      }

      return item.name.toLowerCase().indexOf(search) > -1;
    });
  }
  
  const filteredItems = computed(() =>
    searchInput.value === ''
      ? JSON.parse(JSON.stringify(props.content))
      : filterArray(JSON.parse(JSON.stringify(props.content)), searchInput.value)
  )

  const onEscape = () => {
    searchInput.value = ''
  }

  const onOpen = () => {
    onEscape()
    emit('update:modelValue', selectedItems)
  }

  const selectItems = props.disableSearch ? JSON.parse(JSON.stringify(props.content)) : filteredItems
</script>

<template>
  <ComboboxRoot
    v-model="selectedItems" 
    v-model:searchInput="searchInput"
    class="relative" 
    :multiple="multiple"
    @update:open="onOpen"
  >
    <ComboboxAnchor class="relative w-full min-h-[41px] flex bg-zero-white dark:bg-zero-black border border-zero-light dark:border-zero-dark rounded-md focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-light dark:focus-within:ring-primary-dark focus-within:ring-offset-1 focus-within:ring-offset-primary-light dark:focus-within:ring-offset-primary-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus-within:shadow-[0_0_0_2px] focus-within:shadow-primary-light dark:focus-within:shadow-primary-dark transition ease-in-out duration-500 disabled:opacity-25">
      <div class="w-full">
        <div class="flex flex-wrap gap-1 items-center my-[6px] ml-2">
          <div
            v-for="item in showContent" 
            :class= "multiple ? 
              'p-1 flex items-center text-zero-light dark:text-zero-dark rounded-md placeholder:text-xs sm:placeholder:text-sm text-xs sm:text-sm bg-zero-light dark:bg-zero-dark ring-0 border border-zero-light dark:border-zero-dark' : 
              'items-center text-zero-light dark:text-zero-dark rounded-md placeholder:text-xs sm:placeholder:text-sm bg-zero-white dark:bg-zero-black'
            " 
          >
            {{ $t(item.name) }}
            <button v-if="multiple" @click="removeItem(item.id)" type="button" class="ml-1 rounded-full focus:outline-none focus:ring-2 focus:ring-zero-light dark:focus:ring-zero-dark focus:ring-offset-1 focus:ring-offset-zero-light dark:focus:ring-offset-zero-dark">
              <Icon 
                icon="mdi:close-circle-outline" 
                class="w-4 h-4 text-zero-light dark:text-zero-dark cursor-pointer" 
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
              :required="required && showContent.length === 0"
              autocomplete="off"
              class="p-0 w-full bg-transparent text-ellipsis border-0 outline-0 focus:ring-0 placeholder:text-sm placeholder-primary-dark/20 dark:placeholder-primary-dark/20 text-zero-light dark:text-zero-dark" 
            />
          </ComboboxTrigger>
        </div>
      </div>
      <ComboboxTrigger>
        <div class="inset-y-0 right-0 mr-2 flex items-center pointer-events-none">
          <button :id="id" :name="name" type="button" class="ml-1 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark">
            <Icon icon="mdi:chevron-down" class="w-6 h-6 text-zero-light dark:text-zero-dark" />
          </button>
        </div>
      </ComboboxTrigger>
    </ComboboxAnchor>

    <ComboboxContent 
      class="absolute z-[4] w-full mt-1 min-w-[160px] bg-white overflow-hidden bg-zero-white dark:bg-zero-black text-zero-light dark:text-zero-dark rounded-md border border-zero-light dark:border-zero-dark rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark"
      @escapeKeyDown="onEscape"
      @pointerDownOutside="onEscape"
      @closeAutoFocus="onEscape"
    >
      <ComboboxViewport class="p-[5px] max-h-60">
        <ComboboxEmpty class="text-xs font-medium text-center">{{ $t('No items to show.') }}</ComboboxEmpty>
        <SelectItems :items="selectItems" />
      </ComboboxViewport>
      
    </ComboboxContent>
  </ComboboxRoot>
  <!-- <p v-if="selectItems.length == 0" class="text-xs font-medium text-center">
    <Link :href="route('apps.abilities.index')">
      {{ $t('There are no abilities to select. Click here to manage them.') }}
    </Link>
  </p> -->
</template>