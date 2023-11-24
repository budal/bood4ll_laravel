<script setup lang="ts">
defineProps<{
    required?: boolean;
    value?: string;
}>();
</script>

<template>
    <label class="block font-medium text-sm text-zero-light dark:text-zero-dark">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
        <span v-if="required"> *</span>
    </label>
</template>
