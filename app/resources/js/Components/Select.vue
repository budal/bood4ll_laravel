<script setup lang="ts">
  import { ComboboxAnchor, ComboboxContent, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxLabel, ComboboxRoot, ComboboxSeparator, ComboboxTrigger, ComboboxViewport } from 'radix-vue'
  import { Icon } from '@iconify/vue'
  import { computed, ref } from 'vue'

  const props = withDefaults(
    defineProps<{
      id?: string;
      name?: string;
      modelValue?: any;
      content: any;
      required?: boolean;
      multiple?: boolean;
    }>(),
    {
      multiple: false,
    }
  );

  const selectedContent = ref(props.modelValue ? props.modelValue : [])

  const searchTerm = ref('')

  const filteredContent = computed(() =>
    searchTerm.value === ''
      ? props.content
      : props.content.filter((items: {id: string, title: string}) => {
        return items.title.toLowerCase().includes(searchTerm.value.toLowerCase())
      })
  )

  const showContent = computed(() => props.content.filter((e: any) => { return selectedContent.value.includes(e.id); }))

  const deleteItem = () => alert('aaa')
</script>

<template>
  <ComboboxRoot 
    class="relative" 
    v-model="selectedContent" 
    v-model:searchTerm="searchTerm"
    :multiple="multiple"
  >
    <ComboboxAnchor>
      <div class="relative w-full min-h-[41px] flex bg-zero-light dark:bg-zero-dark border border-zero-light dark:border-zero-dark rounded-md focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-light dark:focus-within:ring-primary-dark focus-within:ring-offset-1 focus-within:ring-offset-primary-light dark:focus-within:ring-offset-primary-dark transition ease-in-out duration-500 disabled:opacity-25">
        <div class="w-full">
          <div class="flex flex-wrap gap-1 items-center my-[6px] ml-2">
            <div v-for="item in showContent" class="p-1 flex items-center text-primary-light dark:text-primary-dark rounded-md text-sm bg-primary-light dark:bg-primary-dark ring-0">
              {{ item.title }}
              <button type="button" class="ml-1 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark">
                <Icon @click="deleteItem" icon="mdi:close-circle-outline" class="w-4 h-4 text-primary-light dark:text-primary-dark cursor-pointer " />
              </button>
            </div>
            <ComboboxInput
              :id="id" 
              :name="name"
              class="ml-1 p-0 grow w-0 bg-transparent text-ellipsis border-0 outline-0 focus:ring-0 placeholder:text-sm placeholder-primary-dark/20 dark:placeholder-primary-dark/20 text-zero-light dark:text-zero-dark" 
            />
          </div>
        </div>
        <ComboboxTrigger>
          <div class="inset-y-0 right-0 mr-2 flex items-center pl-2 pointer-events-none">
            <button type="button" class="ml-1 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark">
              <Icon icon="mdi:chevron-down" class="w-6 h-6 text-primary-light dark:text-primary-dark" />
            </button>
          </div>
        </ComboboxTrigger>
      </div>  
    </ComboboxAnchor>
    <ComboboxContent class="absolute w-full mt-1 min-w-[160px] bg-white overflow-hidden bg-zero-light dark:bg-zero-dark text-zero-light dark:text-zero-dark rounded-md border border-zero-light dark:border-zero-dark rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark">
      <ComboboxViewport class="p-[5px] max-h-60">
        <ComboboxEmpty class="text-xs font-medium text-center" />
        <ComboboxGroup>
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