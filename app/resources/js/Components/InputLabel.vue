<script setup lang="ts">
import { useAttrs } from "vue";
withDefaults(
    defineProps<{
        required?: boolean;
        value?: string;
        theme?: string;
    }>(),
    {
        theme: "zero",
    },
);
</script>

<template>
    <label
        :class="`block font-medium text-sm text-${theme}-light dark:text-${theme}-dark`"
    >
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
        <span v-if="required"> *</span>
    </label>
</template>
