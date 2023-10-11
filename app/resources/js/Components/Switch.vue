<script setup>
import { ref } from 'vue'
import { Switch } from '@headlessui/vue'

const enabled = ref(false)
</script>

<template>
  <div>
    <Switch
      v-model="enabled"
      :class="enabled ? 'bg-primary-light dark:bg-primary-dark' : 'bg-primary-light dark:bg-primary-dark'"
      class="inline-flex h-7 w-12 items-center rounded-full cursor-pointer duration-500 ease-in-out"
    >
    <span class="sr-only">Enable notifications</span>
    <span
      :class="enabled ? 'translate-x-6' : 'translate-x-1'"
      class="inline-block h-5 w-5 rounded-full bg-primary-dark dark:bg-primary-light transform shadow-lg transition duration-500 ease-in-out"
    />
  </Switch>
  </div>
</template>