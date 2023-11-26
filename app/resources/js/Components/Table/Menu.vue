<script setup lang="ts">
  import { Icon } from '@iconify/vue'
  import Button from '@/Components/Button.vue';
  import {
    DropdownMenuArrow,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuPortal,
    DropdownMenuRoot,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
  } from 'radix-vue'
  import { Link } from '@inertiajs/vue3';
  import { ref } from 'vue'

  defineProps<{
    menu: any;
  }>();

  const tableMenuToggleState = ref(false)
</script>

<template>
  <template v-if="menu.length > 1">
    <DropdownMenuRoot v-model:open="tableMenuToggleState">
      <DropdownMenuTrigger
        as="span"
        class="ml-2 h-full outline-none"
        :aria-label="$t('Table menu')"
      >
        <Button color="primary" type="button" start-icon="mdi:dots-horizontal" class="h-full" />
      </DropdownMenuTrigger>

      <DropdownMenuPortal>
        <DropdownMenuContent
          class="min-w-[220px] overflow-hidden outline-none bg-secondary-light dark:bg-secondary-dark text-primary-dark dark:text-primary-light rounded-md shadow-[0px_10px_38px_-10px_rgba(22,_23,_24,_0.35),_0px_10px_20px_-15px_rgba(22,_23,_24,_0.2)] shadow-primary-light dark:shadow-primary-dark will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
          :align="'end'"
        >
          <DropdownMenuLabel class="leading-[25px] text-center">
            <div class="pt-2 font-xs text-sm text-zero-light/40 dark:text-zero-dark/40">{{ $t('Select an option') }}</div>
          </DropdownMenuLabel>
          <DropdownMenuSeparator class="h-[0.5px] bg-zero-dark/10 dark:bg-zero-light/5" />
          <template v-for="item in menu">
            <Link :href="route(item.route)"> 
              <DropdownMenuItem
                as="span"
                :menu="item.title"
                :class="item.class"
                class="px-[5px] flex pl-[10px] text-sm py-3 text-zero-light dark:text-zero-dark hover:bg-zero-light dark:hover:bg-zero-dark focus:outline-none focus:bg-zero-light dark:focus:bg-zero-dark"
              >
                <Icon :icon="item.icon" class="h-5 w-5 mr-2" />
                {{ $t(item.title) }} 
              </DropdownMenuItem>
            </Link>
          </template>
          <DropdownMenuArrow class="fill-secondary-light dark:fill-secondary-dark" />
        </DropdownMenuContent>
      </DropdownMenuPortal>
    </DropdownMenuRoot>
  </template>
  <template v-else>
    <Link as="span" :href="route(
      typeof menu[0].route === 'string' || menu[0].route instanceof String ? menu[0].route : menu[0].route[0], 
      typeof menu[0].route === 'string' || menu[0].route instanceof String ? '' : menu[0].route[1]
      )" class="text-sm ml-2 h-full"
    >
      <Button color="primary" type="button" class="h-full" :start-icon="menu[0].icon" />
    </Link>
  </template>
</template>
