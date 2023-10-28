<script setup lang="ts">
  const props = defineProps<{
      fallback: string;
  }>();

</script>

<template>
  <div class="inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-600">
    <span class="font-medium text-gray-600 dark:text-gray-300">{{ fallback.charAt(0).toUpperCase() }}</span>
  </div>

</template>