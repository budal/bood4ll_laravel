<script setup>
import { Switch } from '@headlessui/vue'
</script>

<template>
  <Switch
    name="terms-of-service"
    :defaultChecked="true"
    as="template"
    v-slot="{ checked }"
  >
    <button
      :class="checked ? 'bg-primary-light dark:bg-primary-dark' : 'bg-primary-light dark:bg-primary-dark'"
      class="inline-flex h-7 w-12 items-center rounded-full cursor-pointer duration-300 ease-in-out"
    >
      <span class="sr-only">Enable notifications</span>
      <span
        :class="checked ? 'translate-x-6 bg-primary-dark dark:bg-primary-light' : 'translate-x-1 bg-primary-dark/80 dark:bg-primary-light/80'"
        class="inline-block h-5 w-5 rounded-full transform shadow-lg transition duration-300 ease-in-out"
      />
    </button>
  </Switch>
</template>