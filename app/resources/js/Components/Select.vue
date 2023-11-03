<script setup lang="ts">
  import { ref } from 'vue'
  import { ComboboxAnchor, ComboboxContent, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxLabel, ComboboxRoot, ComboboxSeparator, ComboboxTrigger, ComboboxViewport } from 'radix-vue'
  import { Icon } from '@iconify/vue'
  import { computed } from 'vue';

  const props = withDefaults(
    defineProps<{
      id?: string;
      name?: string;
      modelValue: Object | string | number;
      content: any;
      required?: boolean;
      multiple?: boolean;
    }>(),
    {
      multiple: false,
    }
  );

  // const emit = defineEmits(['update:modelValue']);
  
  // const selectedOptions = props.content[props.content.map(function(e: any) { return e.id; }).indexOf(props.modelValue)];
  // const selectedOptionsRef = props.multiple == true ? ref(selectedOptions ? [selectedOptions] : []) : ref(selectedOptions);

  // console.log(props.content)

  // const selectedOptionsRef = props.multiple ? ref([props.content[0]]) : ref(props.content[0])
  const searchTerm = ref('')

  const selectedContent = ref([props.content[0], props.content[2]])

  function filterFunction(list: typeof props.content[number], searchTerm: string) {
    return list.filter((item: { title: string }) => {
      return item.title.toLowerCase().includes(searchTerm.toLowerCase())
    })
  }
</script>

<template>
  <ComboboxRoot 
    class="relative" 
    v-model="selectedContent" 
    :filter-function="filterFunction"
    :multiple="multiple"
  >
    <ComboboxAnchor class="w-full inline-flex">
      <ComboboxTrigger class="w-full">
        <ComboboxInput 
          class="w-full block p-2 placeholder:text-sm placeholder-primary-dark/20 dark:placeholder-primary-dark/20 bg-zero-light dark:bg-zero-dark text-zero-light dark:text-zero-dark rounded-md border border-zero-light dark:border-zero-dark rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark transition ease-in-out duration-500 disabled:opacity-25" 
          placeholder="Placeholder..." 
        />
      </ComboboxTrigger>
    </ComboboxAnchor>
    <ComboboxContent class="absolute z-10 w-full mt-1 min-w-[160px] bg-white overflow-hidden rounded shadow-[0px_10px_38px_-10px_rgba(22,_23,_24,_0.35),_0px_10px_20px_-15px_rgba(22,_23,_24,_0.2)] will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade">
      <ComboboxViewport class="p-[5px] max-h-60">
        <ComboboxEmpty class="text-mauve8 text-xs font-medium text-center py-2" />
        <ComboboxGroup>
          <ComboboxItem
            v-for="item in props.content" 
            :key="item.id"
            :value="item"
            :disabled="item.disabled"
            class="text-[13px] leading-none text-grass11 rounded-[3px] flex items-center h-[25px] pr-[35px] pl-[25px] relative select-none data-[disabled]:text-mauve8 data-[disabled]:pointer-events-none data-[highlighted]:outline-none data-[highlighted]:bg-grass9 data-[highlighted]:text-grass1"
          >
            <ComboboxItemIndicator class="absolute left-0 w-[25px] inline-flex items-center justify-center">
              <Icon icon="radix-icons:check" />
            </ComboboxItemIndicator>
            <span>
              {{ item.title }}
            </span>
          </ComboboxItem>
        </ComboboxGroup>
      </ComboboxViewport>
    </ComboboxContent>
  </ComboboxRoot>
</template>