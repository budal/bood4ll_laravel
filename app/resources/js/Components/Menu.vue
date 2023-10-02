
<script setup>
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { ChevronDownIcon, PlusIcon } from '@heroicons/vue/20/solid'
import Button from './Button.vue';
</script>

<template>
    <Menu as="div" class="relative inline-block text-left">
      <div>
        <MenuButton as="span">
          <Button color="primary" class="ml-2 h-[42px]">
            <PlusIcon class="h-5 w-5" />
          </Button>
        </MenuButton>
      </div>

      <transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <MenuItems
          class="absolute right-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-primary-light dark:bg-primary-dark shadow-lg ring-1 ring-primary-light dark:ring-primary-dark focus:outline-none"
        >
          <div class="px-1 py-1">
            <MenuItem v-slot="{ active }">
              <button
                :class="[
                  active ? 'bg-primary-dark dark:bg-primary-light text-primary-dark dark:text-primary-light' : ' text-primary-light dark:text-primary-dark',
                  'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                ]"
              >
                <ChevronDownIcon
                  :active="active"
                  class="mr-2 h-5 w-5"
                  aria-hidden="true"
                />
                Edit
              </button>
            </MenuItem>
          </div>
        </MenuItems>
      </transition>
    </Menu>
</template>
