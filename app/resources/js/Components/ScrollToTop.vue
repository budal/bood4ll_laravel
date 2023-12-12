<script setup lang="ts">
import Button from "@/Components/Button.vue";

const scrollToTop = () =>
    window.scrollTo({
        top: 0,
        behavior: "smooth",
    });
</script>
<template>
    <Button
        color="info"
        @click="scrollToTop"
        start-icon="mdi:arrow-up"
        rounded="rounded-full"
        class="animate-bounce fixed bottom-12 right-1 sm:right-8"
    />
</template>
