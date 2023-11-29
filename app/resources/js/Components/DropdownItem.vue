<script setup lang="ts">
  import { Icon } from '@iconify/vue'
  import DropdownItem from '@/Components/DropdownItem.vue';
  import {
    DropdownMenuItem,
    DropdownMenuPortal,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
  } from 'radix-vue'
  import { ref } from 'vue'

  defineProps<{
    content: any;
  }>();

  const person = ref('')
  const checkboxOne = ref(false)
</script>

<template>
  <template v-for="item in content">
    <DropdownMenuSub v-if="item.items">
      <DropdownMenuSubTrigger
        class="group px-[5px] flex pl-[25px] leading-none rounded-[3px] items-center relative select-none text-sm py-3 focus:outline-none cursor-pointer data-[state=open]:bg-zero-light data-[state=open]:dark:bg-zero-dark data-[highlighted]:bg-zero-light data-[highlighted]:dark:bg-zero-dark data-[highlighted]:text-zero-light data-[highlighted]:dark:text-zero-dark data-[highlighted]:data-[state=open]:bg-zero-light data-[highlighted]:data-[state=open]:dark:bg-zero-darkdata-[disabled]:text-zero-light/50 data-[disabled]:dark:text-zero-light/50 data-[disabled]:pointer-events-none"
      >
        <Icon :icon="item.icon" class="h-5 w-5 mr-1" />
        {{ $t(item.title) }} 
        <div
          class="ml-auto pl-[20px] text-zero-light dark:text-zero-dark group-data-[highlighted]:text-zero-light/70 group-data-[highlighted]:dark:text-zero-dark/70 group-data-[disabled]:text-zero-light/50 group-data-[disabled]:dark:text-zero-dark/50"
        >
          <Icon icon="mdi:chevron-right" />
        </div>
      </DropdownMenuSubTrigger>
      <DropdownMenuPortal>
        <DropdownMenuSubContent
          class="min-w-[220px] overflow-hidden outline-none bg-secondary-light dark:bg-secondary-dark text-primary-dark dark:text-primary-light rounded-md shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
        >
          <DropdownItem :content="item.items"/>

          <!-- 
          <DropdownMenuCheckboxItem
            v-model:checked="checkboxOne"
            class="group text-[13px] leading-none text-grass11 rounded-[3px] flex items-center h-[25px] px-[5px] relative pl-[25px] select-none outline-none data-[disabled]:text-mauve8 data-[disabled]:pointer-events-none data-[highlighted]:bg-green9 data-[highlighted]:text-green1"
          >
            <DropdownMenuItemIndicator class="absolute left-0 w-[25px] inline-flex items-center justify-center">
              <Icon icon="radix-icons:check" />
            </DropdownMenuItemIndicator>
            Show Bookmarks
            <div
              class="ml-auto pl-[20px] text-mauve11 group-data-[highlighted]:text-white group-data-[disabled]:text-mauve8"
            >
              ⌘+B
            </div>
          </DropdownMenuCheckboxItem> 
          -->

          <!-- 
          <DropdownMenuRadioGroup v-model="person">
            <DropdownMenuRadioItem
              class="text-[13px] leading-none text-grass11 rounded-[3px] flex items-center h-[25px] px-[5px] relative pl-[25px] select-none outline-none data-[disabled]:text-mauve8 data-[disabled]:pointer-events-none data-[highlighted]:bg-green9 data-[highlighted]:text-green1"
              value="pedro"
            >
              <DropdownMenuItemIndicator class="absolute left-0 w-[25px] inline-flex items-center justify-center">
                <Icon icon="radix-icons:dot-filled" />
              </DropdownMenuItemIndicator>
              Pedro Duarte
            </DropdownMenuRadioItem>
            <DropdownMenuRadioItem
              class="text-[13px] leading-none text-grass11 rounded-[3px] flex items-center h-[25px] px-[5px] relative pl-[25px] select-none outline-none data-[disabled]:text-mauve8 data-[disabled]:pointer-events-none data-[highlighted]:bg-green9 data-[highlighted]:text-green1"
              value="colm"
            >
              <DropdownMenuItemIndicator class="absolute left-0 w-[25px] inline-flex items-center justify-center">
                <Icon icon="radix-icons:dot-filled" />
              </DropdownMenuItemIndicator>
              Colm Tuite
            </DropdownMenuRadioItem>
          </DropdownMenuRadioGroup> 
          -->



        </DropdownMenuSubContent>
      </DropdownMenuPortal>
    </DropdownMenuSub>
    <DropdownMenuItem v-else
      as="span"
      :menu="item.title"
      class="group px-[5px] flex pl-[25px] leading-none rounded-[3px] items-center relative select-none text-sm py-3 focus:outline-none cursor-pointer data-[state=open]:bg-zero-light data-[state=open]:dark:bg-zero-dark data-[highlighted]:bg-zero-light data-[highlighted]:dark:bg-zero-dark data-[highlighted]:text-zero-light data-[highlighted]:dark:text-zero-dark data-[highlighted]:data-[state=open]:bg-zero-light data-[highlighted]:data-[state=open]:dark:bg-zero-darkdata-[disabled]:text-zero-light/50 data-[disabled]:dark:text-zero-light/50 data-[disabled]:pointer-events-none"
    >
      <Icon :icon="item.icon" class="h-5 w-5 mr-1" />
      {{ $t(item.title) }}
      <div
        class="ml-auto mr-2 pl-[20px] text-zero-light dark:text-zero-dark group-data-[highlighted]:text-zero-light/70 group-data-[highlighted]:dark:text-zero-dark/70 group-data-[disabled]:text-zero-light/50 group-data-[disabled]:dark:text-zero-dark/50"
      >
        ⌘+T
      </div> 
    </DropdownMenuItem>
  </template>
</template>
