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
      colorFalse: 'secondary',
      modelValue: false,
    }
  );

  const emit = defineEmits(['update:modelValue']);

  emit('update:modelValue', props.modelValue || false)

  const toggleState = ref(props.modelValue || false)
</script>

<template>
  <Toggle
    :id="id" 
    :name="name"
    :value="toggleState"
    v-model:pressed="toggleState"
    @update:pressed="emit('update:modelValue', toggleState)"
    :class="`min-h-[41px] w-full flex items-center justify-center rounded text-base leading-4 data-[state=off]:bg-${colorFalse}-light data-[state=off]:hover:bg-${colorFalse}-light-hover data-[state=off]:dark:bg-${colorFalse}-dark data-[state=off]:dark:hover:bg-${colorFalse}-dark-hover data-[state=off]:text-${colorFalse}-light data-[state=off]:dark:text-${colorFalse}-dark data-[state=off]:border-${colorFalse}-light data-[state=off]:dark:border-${colorFalse}-dark data-[state=on]:bg-${color}-light data-[state=on]:hover:bg-${color}-light-hover data-[state=on]:dark:bg-${color}-dark data-[state=on]:dark:hover:bg-${color}-dark-hover data-[state=on]:text-${color}-light data-[state=on]:dark:text-${color}-dark border data-[state=on]:border-${color}-light data-[state=on]:dark:border-${color}-dark focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus-within:shadow-[0_0_0_2px] focus-within:shadow-primary-light dark:focus-within:shadow-primary-dark transition ease-in-out duration-500 disabled:opacity-25`"
    as-child
  >
      <button>
        <Icon :icon="toggleState ? 'mdi:thumb-up-outline' : 'mdi:thumb-down-outline'" class="w-5 h-5"/>
      </button>
  </Toggle>
</template>