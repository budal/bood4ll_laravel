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
  <NavigationMenuRoot v-model="currentTrigger" class="relative z-[1] flex w-full justify-center">
    <NavigationMenuList class="center m-0 flex list-none rounded-[6px] p-1">
      <template v-for="item in content">
        <template v-if="item.links">
          <NavigationMenuItem>
            <NavigationMenuTrigger
              class="group flex select-none items-center justify-between gap-[2px] mx-3 py-2 font-medium leading-none outline-none border-b-2 border-transparent hover:border-primary-dark dark:hover:border-primary-light focus:border-primary-dark dark:focus:border-primary-light text-sm font-medium block select-none text-sm font-medium text-secondary-light/60 dark:text-secondary-dark/60 hover:text-secondary-light dark:hover:text-secondary-dark focus:text-secondary-light dark:focus:text-secondary-dark"
              :class="routeCurrent.includes(item.route) ? 'border-info-light dark:border-info-dark focus:border-warning-light dark:focus:border-warning-dark' : ''"
            >
              {{ $t(item.title) }}
              <Icon
                icon="radix-icons:caret-down"
                class="text-secondary-light/60 dark:text-secondary-dark/60 group-hover:text-secondary-light dark:group-hover:text-secondary-dark focus:text-secondary-light dark:focus:text-secondary-dark relative top-[1px] transition-transform duration-[250] ease-in group-data-[state=open]:-rotate-180"
                aria-hidden
              />
            </NavigationMenuTrigger>
            <NavigationMenuContent class="data-[motion=from-start]:animate-enterFromLeft data-[motion=from-end]:animate-enterFromRight data-[motion=to-start]:animate-exitToLeft data-[motion=to-end]:animate-exitToRight absolute top-0 left-0 w-full sm:w-auto">
              <div class="container mx-auto grid gap-1 p-1 sm:w-[600px] sm:grid-flow-row sm:grid-cols-3">
                <template v-for="subitem in item.links">
                    <NavigationMenuLink as-child>
                      <Link
                        :href="route(subitem.route)"
                        class="border-transparent bg-secondary-light dark:bg-secondary-dark hover:bg-secondary-light-hover dark:hover:bg-secondary-dark-hover focus:bg-secondary-light-hover dark:focus:bg-secondary-dark-hover  block select-none rounded-[6px] p-3 text-[15px] leading-none no-underline outline-none transition-colors"
                        :class="routeCurrent.includes((route(subitem.route) as unknown) as string) ? 'ring-1 focus-visible:ring ring-info-light dark:ring-info-dark' : ''"
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
              class="text-sm font-medium block select-none mx-3 py-2 leading-none no-underline outline-none border-b-2 border-transparent hover:border-primary-dark dark:hover:border-primary-light focus:border-primary-dark dark:focus:border-primary-light text-secondary-light/60 dark:text-secondary-dark/60 hover:text-secondary-light dark:hover:text-secondary-dark focus:text-secondary-light dark:focus:text-secondary-dark"
              :class="routeCurrent.includes(item.route) ? 'border-info-light dark:border-info-dark focus:border-warning-light dark:focus:border-warning-dark' : ''"
            >
              {{ $t(item.title) }}
            </NavigationMenuLink>
          </NavigationMenuItem>
        </template>

      </template>

      <NavigationMenuIndicator
        class="data-[state=hidden]:opacity-0 duration-200 data-[state=visible]:animate-fadeIn data-[state=hidden]:animate-fadeOut top-full z-[1] flex h-[10px] items-end justify-center overflow-hidden transition-[all,transform_250ms_ease]"
      >
        <div class="relative top-[70%] h-[10px] w-[10px] rotate-[45deg] rounded-tl-[2px] bg-system-light dark:bg-system-dark" />
      </NavigationMenuIndicator>
    </NavigationMenuList>

    <div class="perspective-[2000px] absolute top-full left-0 flex w-full justify-center">
      <NavigationMenuViewport
        class="bg-system-light dark:bg-system-dark data-[state=open]:animate-scaleIn data-[state=closed]:animate-scaleOut relative mt-[10px] h-[var(--radix-navigation-menu-viewport-height)] w-full origin-[top_center] overflow-hidden rounded-[10px] transition-[width,_height] duration-300 sm:w-[var(--radix-navigation-menu-viewport-width)]"
      />
    </div>
  </NavigationMenuRoot>
</template>