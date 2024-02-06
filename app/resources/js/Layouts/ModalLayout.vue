<script setup lang="ts">
import Modal from "@/Components/Modal.vue";
// @ts-expect-error
import { useModal } from "/vendor/emargareten/inertia-modal";

withDefaults(
    defineProps<{
        title?: string;
    }>(),
    {
        title: "",
    },
);
const { show, redirect } = useModal();
</script>

<template>
    <Modal
        :open="show"
        :title="$t(title)"
        @close="redirect({ preserveScroll: true })"
    >
        <div class="max-w-7xl mx-auto">
            <slot />
        </div>
    </Modal>
</template>
