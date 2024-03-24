<script setup lang="ts">
import { Separator } from "radix-vue";

defineProps<{
    showIf?: boolean;
}>();
</script>

<template>
    <Separator
        v-if="showIf !== false"
        class="bg-primary-light dark:bg-primary-dark data-[orientation=horizontal]:h-px data-[orientation=horizontal]:w-full data-[orientation=vertical]:h-full data-[orientation=vertical]:w-px my-[5px]"
    />
</template>
