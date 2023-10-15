<script setup lang="ts">
import { computed } from 'vue'
import { Switch } from '@headlessui/vue';

const emit = defineEmits(['update:checked']);

const props = defineProps<{
  checked: boolean;
  value?: any;
}>();

const proxyChecked = computed({
  get() {
    return props.checked;
  },

  set(val) {
    emit('update:checked', val);
  },
});
</script>

<template>
  <div>
    <Switch
      :value="value"
      v-model="proxyChecked"
      :class="proxyChecked ? 'bg-primary-light dark:bg-primary-dark' : 'bg-primary-light dark:bg-primary-dark'"
      class="inline-flex h-[38px] w-[58px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-300 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75"
    >
      <span class="sr-only">Use setting</span>
      <span
        aria-hidden="true"
        :class="proxyChecked ? 'translate-x-5 bg-primary-dark dark:bg-primary-light' : 'translate-x-0 bg-primary-dark/80 dark:bg-primary-light/80'"
        class="pointer-events-none inline-block h-[34px] w-[34px] transform rounded-full shadow-lg ring-0 transition duration-300 ease-in-out"
      />
    </Switch>
  </div>
</template>