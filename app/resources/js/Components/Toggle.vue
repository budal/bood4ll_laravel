<script setup lang="ts">
  import { Toggle } from 'radix-vue'
  import { Icon } from '@iconify/vue'
  import { ref } from 'vue'

  const props = withDefaults(
    defineProps<{
      id?: string;
      name?: string;
      colorOn?: 'zero' | 'primary' | 'secondary' | 'danger' | 'success' | 'warning' | 'info';
      colorOff?: 'zero' | 'primary' | 'secondary' | 'danger' | 'success' | 'warning' | 'info';
      modelValue?: any;
      isUndefined?: boolean;
    }>(),
    {
      colorOn: 'secondary',
      colorOff: 'secondary',
      modelValue: false,
      isUndefined: false,
    }
  );

  const emit = defineEmits(['update:modelValue']);

  emit('update:modelValue', props.modelValue || false)

  const toggleState = ref(props.modelValue || false)

  const colorOn = props.isUndefined === true ? 'warning' : props.colorOn
  const colorOff = props.isUndefined === true ? 'warning' : props.colorOff

</script>

<template>
  <Toggle
    :id="id" 
    :name="name"
    :value="toggleState"
    v-model:pressed="toggleState"
    @update:pressed="emit('update:modelValue', toggleState)"
    :class="`min-h-[41px] w-full flex items-center justify-center rounded-md text-base leading-4 data-[state=off]:bg-${colorOff}-light data-[state=off]:hover:bg-${colorOff}-light-hover data-[state=off]:dark:bg-${colorOff}-dark data-[state=off]:dark:hover:bg-${colorOff}-dark-hover data-[state=off]:text-${colorOff}-light data-[state=off]:dark:text-${colorOff}-dark data-[state=off]:border-${colorOff}-light data-[state=off]:dark:border-${colorOff}-dark data-[state=on]:bg-${colorOn}-light data-[state=on]:hover:bg-${colorOn}-light-hover data-[state=on]:dark:bg-${colorOn}-dark data-[state=on]:dark:hover:bg-${colorOn}-dark-hover data-[state=on]:text-${colorOn}-light data-[state=on]:dark:text-${colorOn}-dark border data-[state=on]:border-${colorOn}-light data-[state=on]:dark:border-${colorOn}-dark focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus-within:shadow-[0_0_0_2px] focus-within:shadow-primary-light dark:focus-within:shadow-primary-dark transition ease-in-out duration-500 disabled:opacity-25`"
    as-child
    @keydown.enter.prevent
  >
      <button>
        <Icon v-if="toggleState === true && isUndefined === false" icon="mdi:thumb-up-outline" class="w-5 h-5"/>
        <Icon v-if="toggleState === false && isUndefined === false" icon="mdi:thumb-down-outline" class="w-5 h-5"/>
        <Icon v-if="isUndefined === true" icon="mdi:exclamation-thick" class="w-5 h-5"/>
      </button>
  </Toggle>
</template>