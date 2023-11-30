<script setup lang="ts">
  import Button from '@/Components/Button.vue';
  import DropdownItem from '@/Components/DropdownItem.vue';
  import {
    DropdownMenuContent,
    DropdownMenuPortal,
    DropdownMenuRoot,
    DropdownMenuTrigger,
  } from 'radix-vue'
  import { ref } from 'vue'

  defineProps<{
    prefix?: string;
    content: any;
  }>();

  const emit = defineEmits(['select'])

  const toggleState = ref(false)
</script>

<template>
  <template v-if="content.size">
    <DropdownMenuRoot v-model:open="toggleState">
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
          :side-offset="2"
        >
          <DropdownItem :prefix="prefix" :content="content" @select-item="(item) => $emit('select', item)" />
        </DropdownMenuContent>
      </DropdownMenuPortal>
    </DropdownMenuRoot>
  </template>
</template>
