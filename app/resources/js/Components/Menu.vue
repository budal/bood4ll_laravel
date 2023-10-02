
<script setup lang="ts">
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { EllipsisHorizontalIcon, PlusIcon } from '@heroicons/vue/20/solid'
import * as heroIcons from '@heroicons/vue/24/solid';
import Button from './Button.vue';
import { router, useForm, usePage, Link } from '@inertiajs/vue3';

const props = defineProps<{
  menu: any;
}>();

const form = useForm({});
</script>

<template>
    <Menu v-if="menu && menu.length > 1" as="div" class="relative inline-block text-left">
      <div>
        <MenuButton as="span">
          <Button color="primary" class="ml-2 h-[42px]">
            <EllipsisHorizontalIcon class="h-5 w-5" />
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
            <MenuItem v-slot="{ active }" v-for="item in menu">
              <button
                :class="[
                  active ? 'bg-primary-dark dark:bg-primary-light text-primary-dark dark:text-primary-light' : ' text-primary-light dark:text-primary-dark',
                  'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                ]"
                @click="form.get((route(item.route) as unknown) as string)"
              >
                <component 
                  :is="heroIcons[item.icon]" 
                  :active="active"
                  class="mr-2 h-5 w-5"
                  aria-hidden="true" />  
                {{ $t(item.title) }}
              </button>
            </MenuItem>
          </div>
        </MenuItems>
      </transition>
    </Menu>
    <Button color="primary" v-if="menu && menu.length == 1" class="ml-2 h-full" @click="form.get((route(menu[0].route) as unknown) as string)">
      <component 
        :is="heroIcons[menu[0].icon]" 
        :active="menu[0].active"
        class="h-5 w-5"
        aria-hidden="true"
      />  
    </Button>
</template>
