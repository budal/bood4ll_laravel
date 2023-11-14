<script setup lang="ts">
  import { Toggle } from 'radix-vue'
  import { Icon } from '@iconify/vue'
  import { ref } from 'vue'

  const props = withDefaults(
    defineProps<{
      id?: string;
      name?: string;
      color?: 'zero' | 'primary' | 'secondary' | 'danger' | 'success' | 'warning' | 'info';
      colorFalse?: 'zero' | 'primary' | 'secondary' | 'danger' | 'success' | 'warning' | 'info';
      modelValue?: any;
    }>(),
    {
      color: 'secondary',
      colorFalse: 'zero',
      modelValue: false,
    }
  );

  const emit = defineEmits(['update:modelValue']);
  const toggleState = ref(props.modelValue)
</script>

<template>
  <Toggle
    as="child"
    :id="id"
    :name="name"
    :value="toggleState"
    v-model:pressed="toggleState"
    @update:pressed="emit('update:modelValue', toggleState)"
    :class="`min-h-[41px] w-full flex items-center justify-center rounded text-base leading-4 bg-${colorFalse}-light dark:bg-${colorFalse}-dark hover:bg-${color}-light-hover dark:hover:bg-${color}-dark-hover text-${colorFalse}-light dark:text-${colorFalse}-dark hover:text-${color}-light dark:hover:text-${color}-dark data-[state=on]:bg-${color}-light data-[state=on]:text-${color}-light data-[state=on]:dark:bg-${color}-dark data-[state=on]:dark:text-${color}-dark border border-zero-light dark:border-zero-dark focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus-within:shadow-[0_0_0_2px] focus-within:shadow-primary-light dark:focus-within:shadow-primary-dark transition ease-in-out duration-500 disabled:opacity-25`"
  >
    <button type="button" class="focus:outline-none">
      <Icon :icon="toggleState ? 'mdi:thumb-up-outline' : 'mdi:thumb-down-outline'" class="w-5 h-5"/>
    </button>
  </Toggle>
</template>