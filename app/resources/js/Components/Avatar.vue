<script setup lang="ts">
  import { AvatarFallback, AvatarImage, AvatarRoot } from 'radix-vue'
  
  const props = defineProps<{
      fallback: string;
  }>();

</script>

<template>
  <AvatarRoot class="bg-zero-light dark:bg-zero-dark inline-flex h-10 w-10 select-none items-center justify-center overflow-hidden rounded-full align-middle">
    <AvatarFallback class="bg-zero-light dark:bg-zero-dark text-zero-light dark:text-zero-dark leading-1 flex h-full w-full items-center justify-center text-[15px] font-medium">
      {{ fallback.charAt(0).toUpperCase() }}
    </AvatarFallback>
  </AvatarRoot>
</template>