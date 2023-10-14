<script setup lang="ts">
import { ref } from 'vue'
import { Switch } from '@headlessui/vue';

const props = withDefaults(
    defineProps<{
      modelValue: Object | string | number;
      content: any;
      multiple?: boolean;
    }>(),
    {
      multiple: false,
    }
  );

const enabled = ref(false)

</script>

<template>
  <div>
    <Switch
      v-model="enabled"
      :class="enabled ? 'bg-primary-light dark:bg-primary-dark' : 'bg-primary-light dark:bg-primary-dark'"
      class="inline-flex h-[38px] w-[58px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-300 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75"
    >
      <span class="sr-only">Use setting</span>
      <span
        aria-hidden="true"
        :class="enabled ? 'translate-x-5 bg-primary-dark dark:bg-primary-light' : 'translate-x-0 bg-primary-dark/80 dark:bg-primary-light/80'"
        class="pointer-events-none inline-block h-[34px] w-[34px] transform rounded-full shadow-lg ring-0 transition duration-300 ease-in-out"
      />
    </Switch>
  </div>
</template>