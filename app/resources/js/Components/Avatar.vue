<script setup lang="ts">
  defineProps<{
      name?: string;
  }>();
</script>

<template>
  <!-- <div
    class="w-6 h-6 flex items-center justify-center uppercase text-white font-medium shadow"
    :class="avatarClasses"
  >
    <img
      v-if="$attrs.src"
      class="w-full h-full overflow-hidden"
      :class="roundClasses"
      v-bind="$attrs"
      v-on="$listeners"
    >
    <span v-else>
      <slot>{{avatarText}}</slot>
    </span>
  </div> -->

  <div class="inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-600">
    <span class="font-medium text-gray-600 dark:text-gray-300">{{ name[0] }}</span>
  </div>

</template>