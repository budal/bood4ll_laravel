<script setup lang="ts">
import { useDark, useToggle } from "@vueuse/core"
import { MoonIcon, SunIcon } from '@heroicons/vue/20/solid';

const isDark = useDark();
const toggleDark = useToggle(isDark);

</script>

<template>
  <button type="button" @click="toggleDark()" class="mr-5 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark transition ease-in-out duration-500">
    <SunIcon v-if="isDark" class="w-6 h-6 text-primary-light" />
    <MoonIcon v-if="!isDark" class="w-6 h-6 text-primary-dark" />
  </button>
</template>
