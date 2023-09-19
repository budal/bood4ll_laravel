<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'

const props = defineProps<{
    items: any;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out'
);
</script>

<template>
  <div>
    <Popover v-slot="{ open }">
      <PopoverButton class="ring-0">
        <div :class=classes>
          <span class="ml-1 py-5">
            <slot />
          </span>
          <ChevronDownIcon
            class="ml-2 h-5 w-5 text-grey-300 transition duration-150 ease-in-out group-hover:text-opacity-80"
            aria-hidden="true"
          />
        </div>
      </PopoverButton>

      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-1 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-1 opacity-0"
      >
        <PopoverPanel class="absolute left-1/2 z-10 mt-3 -translate-x-1/2 transform px-4 sm:px-0 w-screen max-w-xl md:max-w-3xl lg:max-w-5xl" >
          <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-20">
            <div class="grid gap-8 bg-white dark:bg-gray-800 p-7 md:grid-cols-2 lg:grid-cols-3">
              <Link
                v-for="item in items"
                :key="item.name"
                :href="route(item.route)"
                class="-m-3 flex items-center rounded-lg p-2 transition duration-150 ease-in-out hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus-visible:ring focus-visible:ring-orange-500 focus-visible:ring-opacity-50"
              >
                <div class="flex h-20 w-20 shrink-0 items-center justify-center text-white sm:h-12 sm:w-12" >
                  <div v-html="item.icon" class="h-8 w-8 flex-none text-gray-400" aria-hidden="true"></div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $t(item.title) }}
                  </p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $t(item.description) }}
                  </p>
                </div>
              </Link>
            </div>
          </div>
        </PopoverPanel>
      </transition>
    </Popover>
  </div>
</template>
