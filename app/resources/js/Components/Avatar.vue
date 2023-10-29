<script setup lang="ts">
  import { AvatarFallback, AvatarImage, AvatarRoot } from 'radix-vue'
  
  const props = defineProps<{
      fallback: string;
  }>();

</script>

<template>
  <AvatarRoot class="bg-primary-light dark:bg-primary-dark inline-flex h-10 w-10 select-none items-center justify-center overflow-hidden rounded-full align-middle">
    <AvatarFallback class="bg-primary-light dark:bg-primary-dark text-primary-light dark:text-primary-dark leading-1 flex h-full w-full items-center justify-center text-[15px] font-medium">
      {{ fallback.charAt(0).toUpperCase() }}
    </AvatarFallback>
  </AvatarRoot>
</template>