<script setup lang="ts">
import { onMounted, ref } from 'vue';

defineProps<{
    modelValue: string;
}>();

defineEmits(['update:modelValue']);

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="w-full block p-2 placeholder:text-sm placeholder-primary-dark/20 dark:placeholder-primary-dark/20 bg-zero-light dark:bg-zero-dark autofill:bg-zero-light dark:autofill:bg-zero-dark text-zero-light dark:text-zero-dark rounded-md border border-zero-light dark:border-zero-dark rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus-within:shadow-[0_0_0_2px] focus-within:shadow-primary-light dark:focus-within:shadow-primary-dark transition ease-in-out duration-500 disabled:opacity-25"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        ref="input"
    />
</template>
