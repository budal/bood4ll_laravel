<script setup lang="ts">
  import { Icon } from '@iconify/vue'
  import Button from '@/Components/Button.vue';
  import {
    DropdownMenuArrow,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuItemIndicator,
    DropdownMenuPortal,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuRoot,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
  } from 'radix-vue'
  import { Link } from '@inertiajs/vue3';
  import { ref } from 'vue'

  const props = defineProps<{
    menu: any;
    content: any;
  }>();

  const tableMenuToggleState = ref(false)
  


  const person = ref('')
  const checkboxOne = ref(false)




  // console.log(props.content)

</script>

<template>
  <template v-if="content.size > 1">
      <DropdownMenuRoot v-model:open="tableMenuToggleState">
      <DropdownMenuTrigger
        as="span"
        class="h-full outline-none"
      >
        <Button color="info" type="button" start-icon="mdi:dots-horizontal" class="h-full" />
      </DropdownMenuTrigger>

      <DropdownMenuPortal>
        <DropdownMenuContent
          class="min-w-[220px] overflow-hidden outline-none bg-secondary-light dark:bg-secondary-dark text-primary-dark dark:text-primary-light rounded-md shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
          :align="'start'"
        >
          <DropdownMenuSeparator class="h-[0.5px] bg-zero-dark/10 dark:bg-zero-light/5" />
          <template v-for="item in content">





            <DropdownMenuSub v-if="item.items">
              <DropdownMenuSubTrigger
                class="px-[5px] flex pl-[25px] leading-none rounded-[3px] items-center relative select-none text-sm py-3 focus:outline-none cursor-pointer data-[state=open]:bg-zero-light data-[state=open]:dark:bg-zero-dark data-[highlighted]:bg-zero-light data-[highlighted]:dark:bg-zero-dark data-[highlighted]:text-zero-light data-[highlighted]:dark:text-zero-dark data-[highlighted]:data-[state=open]:bg-zero-light data-[highlighted]:data-[state=open]:dark:bg-zero-darkdata-[disabled]:text-zero-light/50 data-[disabled]:dark:text-zero-light/50 data-[disabled]:pointer-events-none"
              >
                <Icon :icon="item.icon" class="h-5 w-5 mr-2" />
                {{ $t(item.title) }} 
                <div
                  class="ml-auto pl-[20px] text-zero-light dark:text-zero-dark group-data-[highlighted]:text-zero-light group-data-[highlighted]:dark:text-zero-dark group-data-[disabled]:text-zero-light/50group-data-[disabled]:dark:text-zero-dark/50"
                >
                  <Icon icon="mdi:chevron-right" />
                </div>
              </DropdownMenuSubTrigger>
              <DropdownMenuPortal>
                <DropdownMenuSubContent
                  class="min-w-[220px] overflow-hidden outline-none bg-secondary-light dark:bg-secondary-dark text-primary-dark dark:text-primary-light rounded-md shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
                  :side-offset="2"
                >
                  <DropdownMenuItem
                    v-for="subitem in item.items"
                    class="px-[5px] flex pl-[25px] leading-none rounded-[3px] items-center relative select-none text-sm py-3 focus:outline-none cursor-pointer data-[state=open]:bg-zero-light data-[state=open]:dark:bg-zero-dark data-[highlighted]:bg-zero-light data-[highlighted]:dark:bg-zero-dark data-[highlighted]:text-zero-light data-[highlighted]:dark:text-zero-dark data-[highlighted]:data-[state=open]:bg-zero-light data-[highlighted]:data-[state=open]:dark:bg-zero-darkdata-[disabled]:text-zero-light/50 data-[disabled]:dark:text-zero-light/50 data-[disabled]:pointer-events-none"
                  >
                    {{ $t(subitem.title) }} 
                  </DropdownMenuItem>


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
              class="px-[5px] flex pl-[25px] leading-none rounded-[3px] items-center relative select-none text-sm py-3 focus:outline-none cursor-pointer data-[state=open]:bg-zero-light data-[state=open]:dark:bg-zero-dark data-[highlighted]:bg-zero-light data-[highlighted]:dark:bg-zero-dark data-[highlighted]:text-zero-light data-[highlighted]:dark:text-zero-dark data-[highlighted]:data-[state=open]:bg-zero-light data-[highlighted]:data-[state=open]:dark:bg-zero-darkdata-[disabled]:text-zero-light/50 data-[disabled]:dark:text-zero-light/50 data-[disabled]:pointer-events-none"
            >
              <Icon :icon="item.icon" class="h-5 w-5 mr-2" />
              {{ $t(item.title) }} 
            </DropdownMenuItem>
          </template>
          <DropdownMenuArrow class="fill-secondary-light dark:fill-secondary-dark" />
        </DropdownMenuContent>
      </DropdownMenuPortal>
    </DropdownMenuRoot>
  </template>
</template>
