<script setup lang="ts">
import { Toggle } from 'radix-vue'
import { Icon } from '@iconify/vue'
import { ref } from 'vue'

const toggleState = ref(false)
</script>

<template>
  <Toggle
    v-model:pressed="toggleState" aria-label="Toggle italic"
    class="min-h-[41px] w-full flex items-center justify-center rounded text-base leading-4 bg-zero-light dark:bg-zero-dark hover:bg-success-light-hover dark:hover:bg-success-dark-hover text-zero-light dark:text-zero-dark hover:text-success-light dark:hover:text-success-dark data-[state=on]:bg-success-light data-[state=on]:text-success-light border border-zero-light dark:border-zero-dark focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus-within:shadow-[0_0_0_2px] focus-within:shadow-primary-light dark:focus-within:shadow-primary-dark transition ease-in-out duration-500 disabled:opacity-25"
  >
    <Icon icon="radix-icons:font-italic" class="w-[15px] h-[15px]" />
  </Toggle>
</template>