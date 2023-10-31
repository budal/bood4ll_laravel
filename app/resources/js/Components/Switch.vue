<script setup lang="ts">
  import { ref } from 'vue'
  import { SwitchRoot, SwitchThumb } from 'radix-vue'

  const props = defineProps<{
    checked: boolean;
    value?: any;
  }>();

  const emit = defineEmits(['update:checked']);

  const proxyChecked = ref(props.checked);
</script>

<template>
  <div>
    <SwitchRoot
      v-model:checked="proxyChecked"
      class="w-[42px] h-[25px] flex bg-primary-light dark:bg-primary-dark shadow-sm rounded-full relative data-[state=checked]:bg-black cursor-pointer"
    >
    <span class="sr-only">{{ value }}</span>
    <SwitchThumb
        class="block w-[21px] h-[21px] my-auto shadow-sm rounded-full transition-transform duration-100 translate-x-0.5 will-change-transform data-[state=checked]:translate-x-[19px]"
        :class="proxyChecked ? 'bg-primary-dark dark:bg-primary-light' : 'bg-primary-dark/80 dark:bg-primary-light/80'"
      />
    </SwitchRoot>
  </div>
</template>