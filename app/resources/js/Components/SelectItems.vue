<script setup lang="ts">
  import { ComboboxGroup, ComboboxItem, ComboboxItemIndicator } from 'radix-vue'
  import { Icon } from '@iconify/vue'

  const props = withDefaults(
    defineProps<{
      items: any;
      margin?: any;
    }>(),
    {
      margin: 0
    }
  );
</script>

<template>
  <ComboboxGroup>
    <template v-for="item in items" :key="item.id">
      <ComboboxItem
        class="text-sm p-3 leading-none pr-[35px] pl-[25px] relative select-none data-[disabled]:opacity-25 data-[disabled]:pointer-events-none data-[highlighted]:outline-none data-[highlighted]:bg-zero-light-hover dark:data-[highlighted]:bg-zero-dark-hover cursor-pointer"
        :value="item.id"
        :disabled="item.disabled"
      >
        <ComboboxItemIndicator
          class="absolute left-0 w-[25px] inline-flex items-center justify-center"
        >
          <Icon icon="radix-icons:check" />
        </ComboboxItemIndicator>
        <span>{{ $t(item.name) }}</span>
      </ComboboxItem>
      <SelectItems class="ml-5" :items="item.children_recursive" />
    </template>
  </ComboboxGroup>
</template>