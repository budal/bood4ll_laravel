<script setup lang="ts">
  import { ComboboxAnchor, ComboboxContent, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxLabel, ComboboxRoot, ComboboxSeparator, ComboboxTrigger, ComboboxViewport } from 'radix-vue'
  import { Icon } from '@iconify/vue'
  import { ref, computed } from 'vue';

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
    <template v-for="(item, key) in items">
      <!-- <ComboboxLabel v-if="key == 0" class="px-[25px] text-xs leading-[25px] text-mauve11">
        {{ item.name }}
      </ComboboxLabel> -->
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
        <span>
          {{ item.name }} ({{ margin }} - {{ key }})
        </span>
      </ComboboxItem>
      <SelectItems :items="item.children_recursive" />
    </template>
  </ComboboxGroup>
</template>