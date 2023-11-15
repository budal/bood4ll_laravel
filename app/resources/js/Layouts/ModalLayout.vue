<script setup lang="ts">
    import Modal from '@/Components/Modal.vue'
    import { useDark } from "@vueuse/core"
    import { useModal } from "/vendor/emargareten/inertia-modal"

    withDefaults(
        defineProps<{
            title?: string;
        }>(),
        {
            title: '',
        }
    );
    const isDark = useDark();
    const { show, close, redirect } = useModal()
</script>

<template>
    <Modal 
        :open="show" 
        :title="$t(title)" 
        @close="redirect"
    >
        <div class="max-w-7xl mx-auto">
            <slot />
        </div>
    </Modal>
</template>
