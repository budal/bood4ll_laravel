<script setup lang="ts">
  import { ref } from 'vue'
  import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
  } from '@headlessui/vue'
  import { Icon } from '@iconify/vue'

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

  const emit = defineEmits(['update:modelValue']);

  const selectedOptions = props.content[props.content.map(function(e: any) { return e.id; }).indexOf(props.modelValue)];
  const selectedOptionsRef = props.multiple == true ? ref(selectedOptions ? [selectedOptions] : []) : ref(selectedOptions);

</script>

<template>
  <Listbox 
    v-model="selectedOptionsRef"
    @update:modelValue="value => emit('update:modelValue', props.multiple == true ? value.map((i: any) => i.id) : value.id)"
    :required="required"
    :multiple="multiple"
  >
    <div class="relative mt-1">
      <ListboxButton
        :id="id"
        :name="name"
        class="w-full block cursor-default rounded-md bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:outline-none dark:bg-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-500 focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-gray-300 dark:focus:ring-gray-500 disabled:opacity-25 transition ease-in-out duration-500"
        v-slot="{ value }"
      >
        <span class="block truncate">
          {{  
            value ? (
              multiple == true ? (
                value.length > 0 ? 
                  "("+ value.length +") " + value.map((item: any) => $t(item.title)).join(', ') : 
                  $t('Select one or more options')
              ) : $t(value.title)
            ) : $t('Select an option')
          }}
        </span>
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2" >
          <Icon icon="mdi:chevron-down" class="h-5 w-5 text-gray-400" />
        </span>
      </ListboxButton>
      <transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-100 ease-out"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <ListboxOptions
          class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md py-1 text-base shadow-lg sm:text-sm bg-white dark:bg-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-500 focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-gray-300 dark:focus:ring-gray-500 disabled:opacity-25 transition ease-in-out duration-500"
        >
          <ListboxOption
            v-for="item in content"
            as="template"
            :key="item.id"
            :value="item"
            :disabled="item.disabled"
            v-slot="{ active, selected }"
          >
            <li
              :class="[
                  active ? 'bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-gray-300' : 'text-gray-700 dark:text-gray-300',
                  'relative cursor-default select-none py-2 pl-10 pr-4',
                ]"
            >
              <span
                :class="[
                  selected ? 'font-medium' : 'font-normal',
                  item.disabled ? 'opacity-50 dark:opacity-25 italic line-through' : '',
                  'block truncate',
                ]"
                >{{ $t(item.title) }}
              </span>
              <span
                v-if="selected"
                class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-600 dark:text-gray-300"
              >
                <Icon icon="mdi:chevron-down" class="h-5 w-5 text-gray-400" />
              </span>
            </li>
          </ListboxOption>
          <ListboxOption v-if="content.length == 0" disabled>
            <li class="relative cursor-default select-none py-2 pl-10 pr-4">
              <span class="opacity-50 dark:opacity-25 italic">{{ $t('No items to show.') }}</span>
            </li>
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </div>
  </Listbox>
</template>