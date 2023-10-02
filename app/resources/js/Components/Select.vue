<script setup lang="ts">
  import { ref } from 'vue'
  import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
  } from '@headlessui/vue'
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';

const props = defineProps<{
    modelValue: Object | string | number;
    content: any;
}>(); 

const emit = defineEmits(['update:modelValue']);

const selectedPerson = ref(props.content[0])


console.log(props.modelValue)
</script>

<template>
  <Listbox 
    v-model="selectedPerson"
  >
    <div class="relative mt-1">
      <ListboxButton
        class="w-full block cursor-default rounded-md bg-white py-2.5 pl-3 pr-10 text-left sm:text-sm shadow-sm focus:outline-none dark:bg-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-500 focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-gray-300 dark:focus:ring-gray-500 disabled:opacity-25 transition ease-in-out duration-500"
        v-slot="{ value }"
      >
        <span class="block truncate">{{ $t(value.title) }}</span>
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2" >
          <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
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
          v-for="person in props.content"
          as="template"
          :key="person.id"
          :value="person"
          :disabled="person.disabled"
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
                person.disabled ? 'opacity-50 dark:opacity-25 italic line-through' : '',
                'block truncate',
              ]"
              >{{ $t(person.title) }}
            </span>
            <span
              v-if="selected"
              class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-600 dark:text-gray-300"
            >
              <CheckIcon class="h-5 w-5" aria-hidden="true" />
            </span>
          </li>
        </ListboxOption>
      </ListboxOptions>


    </transition>





    </div>
  </Listbox>
</template>