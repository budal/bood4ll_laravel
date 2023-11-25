<script setup lang="ts">
  import { Icon } from '@iconify/vue'
  import { ref } from 'vue'
  import {
    NavigationMenuContent,
    NavigationMenuIndicator,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuRoot,
    NavigationMenuTrigger,
    NavigationMenuViewport,
  } from 'radix-vue'
import { Link } from '@inertiajs/vue3';

  defineProps<{
    content: any;
  }>();

  const currentTrigger = ref('')
  const routeCurrent = window.location.href;
</script>

<template>
  <div>
    <NavigationMenuRoot v-model="currentTrigger" class="z-[100] flex w-full justify-center">
      <NavigationMenuList class="center m-0 flex list-none rounded-[6px] p-1">
        <template v-for="item in content">
          <template v-if="item.links">
            <NavigationMenuItem>
              <NavigationMenuTrigger
                class="group flex select-none items-center justify-between gap-[2px] mx-3 py-[23px] font-medium leading-none outline-none border-b-2 border-transparent text-sm font-medium block select-none text-sm font-medium transition ease-in-out duration-500"
                :class="routeCurrent.includes(item.route) ? 
                  'border-warning-light dark:border-warning-dark focus:border-info-light dark:focus:border-info-dark text-secondary-light dark:text-secondary-dark' : 
                  'hover:border-secondary-light dark:hover:border-secondary-dark focus:border-primary-light dark:focus:border-primary-dark text-secondary-light/60 dark:text-secondary-dark/60 hover:text-secondary-light dark:hover:text-secondary-dark focus:text-secondary-light dark:focus:text-secondary-dark'"
              >
                {{ $t(item.title) }}
                <Icon
                  icon="radix-icons:caret-down"
                  class="text-secondary-light/60 dark:text-secondary-dark/60 group-hover:text-secondary-light dark:group-hover:text-secondary-dark focus:text-secondary-light dark:focus:text-secondary-dark relative top-[1px] transition-transform duration-[250] ease-in group-data-[state=open]:-rotate-180"
                  aria-hidden
                />
              </NavigationMenuTrigger>
              <NavigationMenuContent class="z-[30] top-0 data-[motion=from-start]:animate-enterFromLeft data-[motion=from-end]:animate-enterFromRight data-[motion=to-start]:animate-exitToLeft data-[motion=to-end]:animate-exitToRight absolute top-0 left-0 w-full sm:w-auto">
                <div class="container mx-auto grid gap-2 p-2 w-screen max-w-xl md:max-w-3xl lg:max-w-5xl sm:grid-flow-row sm:grid-cols-2 lg:grid-cols-3">
                  <template v-for="subitem in item.links">
                      <NavigationMenuLink as-child>
                        <Link
                          :href="route(subitem.route)"
                          class="border-transparent bg-secondary-light dark:bg-secondary-dark hover:bg-secondary-light-hover/50 dark:hover:bg-secondary-dark/50 focus:bg-secondary-light-hover/50 dark:focus:bg-secondary-dark/50 block select-none rounded-[6px] p-3 text-[15px] leading-none no-underline outline-none transition-colors"
                          :class="routeCurrent.includes(route(subitem.route)) ?
                            'ring-1 focus-visible:ring ring-warning-light dark:ring-warning-dark' : 
                            ''"
                        >
                          <div class="text-secondary-light dark:text-secondary-dark mb-[5px] font-medium leading-[1.2]">
                            {{ $t(subitem.title) }}
                          </div>
                          <p class="text-xs text-secondary-light/60 dark:text-secondary-dark/60 my-0 leading-[1.4]">
                            {{ $t(subitem.description) }}
                          </p>
                        </Link>
                      </NavigationMenuLink>
                  </template>
                </div>
              </NavigationMenuContent>
            </NavigationMenuItem>
          </template>
          <template v-else>
            <NavigationMenuItem>
              <NavigationMenuLink
                :href="route($t(item.route))"
                class="text-sm font-medium block select-none mx-3 py-[23px] leading-none no-underline outline-none border-b-2 border-transparent transition ease-in-out duration-500"
                :class="routeCurrent.includes(item.route) ? 
                  'border-warning-light dark:border-warning-dark focus:border-info-light dark:focus:border-info-dark text-secondary-light dark:text-secondary-dark ' : 
                  'hover:border-secondary-light dark:hover:border-secondary-dark focus:border-primary-light dark:focus:border-primary-dark text-secondary-light/60 dark:text-secondary-dark/60 hover:text-secondary-light dark:hover:text-secondary-dark focus:text-secondary-light dark:focus:text-secondary-dark'"
              >
                {{ $t(item.title) }}
              </NavigationMenuLink>
            </NavigationMenuItem>
          </template>
        </template>
        <NavigationMenuIndicator
          class="data-[state=hidden]:opacity-0 duration-200 data-[state=visible]:animate-fadeIn data-[state=hidden]:animate-fadeOut top-full z-[1] flex h-[10px] items-end justify-center overflow-hidden transition-[all,transform_250ms_ease]"
        >
          <div class="relative top-[10%] h-[10px] w-[10px] rotate-[45deg] rounded-tl-[2px] bg-zero-light-hover dark:bg-zero-dark-hover" />
        </NavigationMenuIndicator>
      </NavigationMenuList>
      <div class="perspective-[2000px] absolute top-full left-0 flex w-full justify-center">
        <NavigationMenuViewport
          class="bg-zero-light-hover dark:bg-zero-dark-hover data-[state=open]:animate-scaleIn data-[state=closed]:animate-scaleOut relative mt-[10px] h-[var(--radix-navigation-menu-viewport-height)] w-full origin-[top_center] overflow-hidden rounded-[10px] transition-[width,_height] duration-300 sm:w-[var(--radix-navigation-menu-viewport-width)]"
        />
      </div>
    </NavigationMenuRoot>
  </div>
</template>