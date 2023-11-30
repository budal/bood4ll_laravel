<script setup lang="ts">
  import { Icon } from '@iconify/vue'
  import DropdownItem from '@/Components/DropdownItem.vue';
  import {
DropdownMenuCheckboxItem,
    DropdownMenuItem,
    DropdownMenuItemIndicator,
    DropdownMenuPortal,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
  } from 'radix-vue'
  import { ref } from 'vue'

  defineProps<{
    prefix?: string;
    content: any;
  }>();

  const person = ref('active')
  const checkboxOne = ref(true)
</script>

<template>
  <template v-for="item in content">
    <DropdownMenuSub v-if="item.items">
      <DropdownMenuSubTrigger
        class="group px-[5px] flex pl-[25px] leading-none rounded-[3px] items-center relative select-none text-sm py-3 focus:outline-none cursor-pointer data-[state=open]:bg-zero-light data-[state=open]:dark:bg-zero-dark data-[highlighted]:bg-zero-light data-[highlighted]:dark:bg-zero-dark data-[highlighted]:text-zero-light data-[highlighted]:dark:text-zero-dark data-[highlighted]:data-[state=open]:bg-zero-light data-[highlighted]:data-[state=open]:dark:bg-zero-dark data-[disabled]:text-zero-light/50 data-[disabled]:dark:text-zero-light/50 data-[disabled]:pointer-events-none"
      >
        <Icon v-if="item.icon" :icon="item.icon" class="h-5 w-5 mr-1" />
        {{ $t(item.title) }} 
        <div class="ml-auto pl-[20px] text-zero-light dark:text-zero-dark group-data-[highlighted]:text-zero-light/70 group-data-[highlighted]:dark:text-zero-dark/70 group-data-[disabled]:text-zero-light/50 group-data-[disabled]:dark:text-zero-dark/50" >
          <Icon icon="mdi:chevron-right" />
        </div>
      </DropdownMenuSubTrigger>
      <DropdownMenuPortal>
        <DropdownMenuSubContent
          class="min-w-[220px] overflow-hidden outline-none bg-secondary-light dark:bg-secondary-dark text-primary-dark dark:text-primary-light rounded-md shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
        >
          <DropdownItem 
            v-if="!item.type"
            :prefix="prefix"
            :content="item.items" 
          />

          <template v-if="item.type == 'check'">
            <DropdownMenuCheckboxItem
              v-for="check in item.items"
              v-model:checked="checkboxOne"
              class="group px-[5px] flex pl-[25px] leading-none rounded-[3px] items-center relative select-none text-sm py-3 focus:outline-none cursor-pointer data-[state=open]:bg-zero-light data-[state=open]:dark:bg-zero-dark data-[highlighted]:bg-zero-light data-[highlighted]:dark:bg-zero-dark data-[highlighted]:text-zero-light data-[highlighted]:dark:text-zero-dark data-[highlighted]:data-[state=open]:bg-zero-light data-[highlighted]:data-[state=open]:dark:bg-zero-dark data-[disabled]:text-zero-light/50 data-[disabled]:dark:text-zero-light/50 data-[disabled]:pointer-events-none"
            >
              <DropdownMenuItemIndicator class="absolute left-0 w-[25px] inline-flex items-center justify-center">
                <Icon icon="radix-icons:check" />
              </DropdownMenuItemIndicator>
              <Icon v-if="check.icon" :icon="check.icon" class="h-5 w-5 mr-1" />
              {{ $t(check.title) }}
              <div v-if="check.shortcut" class="ml-auto mr-2 pl-[20px] text-zero-light dark:text-zero-dark group-data-[highlighted]:text-zero-light/70 group-data-[highlighted]:dark:text-zero-dark/70 group-data-[disabled]:text-zero-light/50 group-data-[disabled]:dark:text-zero-dark/50" >
                {{ check.shortcut }}
              </div> 
            </DropdownMenuCheckboxItem> 
          </template>

          <template v-if="item.type == 'radio'">
            <DropdownMenuRadioGroup 
              v-if="item.type == 'radio'"
              v-model="person"
            >
              <DropdownMenuRadioItem
                v-for="radio in item.items"
                class="group px-[5px] flex pl-[25px] leading-none rounded-[3px] items-center relative select-none text-sm py-3 focus:outline-none cursor-pointer data-[state=open]:bg-zero-light data-[state=open]:dark:bg-zero-dark data-[highlighted]:bg-zero-light data-[highlighted]:dark:bg-zero-dark data-[highlighted]:text-zero-light data-[highlighted]:dark:text-zero-dark data-[highlighted]:data-[state=open]:bg-zero-light data-[highlighted]:data-[state=open]:dark:bg-zero-dark data-[disabled]:text-zero-light/50 data-[disabled]:dark:text-zero-light/50 data-[disabled]:pointer-events-none"
                :value="radio.id"
              >
                <DropdownMenuItemIndicator class="absolute left-0 w-[25px] inline-flex items-center justify-center">
                  <Icon icon="radix-icons:dot-filled" />
                </DropdownMenuItemIndicator>
                <Icon v-if="radio.shortcut" :icon="radio.icon" class="h-5 w-5 mr-1" />
                {{ $t(radio.title) }}
                <div v-if="radio.shortcut" class="ml-auto mr-2 pl-[20px] text-zero-light dark:text-zero-dark group-data-[highlighted]:text-zero-light/70 group-data-[highlighted]:dark:text-zero-dark/70 group-data-[disabled]:text-zero-light/50 group-data-[disabled]:dark:text-zero-dark/50" >
                  {{ radio.shortcut }}
                </div> 
              </DropdownMenuRadioItem>
            </DropdownMenuRadioGroup> 
          </template>
        </DropdownMenuSubContent>
      </DropdownMenuPortal>
    </DropdownMenuSub>
    <template v-else>
      <DropdownMenuSeparator v-if="item.title == '-'" class="h-[0.5px] bg-zero-dark/10 dark:bg-zero-light/5" />
      <template v-else>
        <DropdownMenuItem 
          as="span"
          :menu="item.title"
          :disabled="item.disabled"
          class="group px-[5px] flex pl-[25px] leading-none rounded-[3px] items-center relative select-none text-sm py-3 focus:outline-none cursor-pointer data-[state=open]:bg-zero-light data-[state=open]:dark:bg-zero-dark data-[highlighted]:bg-zero-light data-[highlighted]:dark:bg-zero-dark data-[highlighted]:text-zero-light data-[highlighted]:dark:text-zero-dark data-[highlighted]:data-[state=open]:bg-zero-light data-[highlighted]:data-[state=open]:dark:bg-zero-dark data-[disabled]:text-zero-light/50 data-[disabled]:dark:text-zero-light/50 data-[disabled]:pointer-events-none"
        >
          <Icon v-if="item.icon" :icon="item.icon" class="h-5 w-5 mr-1" />
          {{ $t(item.title) }}

          <div v-if="item.shortcut" class="ml-auto mr-2 pl-[20px] text-zero-light dark:text-zero-dark group-data-[highlighted]:text-zero-light/70 group-data-[highlighted]:dark:text-zero-dark/70 group-data-[disabled]:text-zero-light/50 group-data-[disabled]:dark:text-zero-dark/50" >
            {{ item.shortcut }}
          </div> 
        </DropdownMenuItem>
      </template>
    </template>
  </template>
</template>
