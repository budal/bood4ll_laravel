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
        class="w-full block p-2 pl-9 placeholder:text-sm dark:placeholder-gray-400 dark:bg-gray-900 dark:text-gray-300 rounded-md border border-gray-300 dark:border-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-gray-300 dark:focus:ring-gray-500 disabled:opacity-25 transition ease-in-out duration-500 shadow-sm"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        ref="input"
    />
</template>
