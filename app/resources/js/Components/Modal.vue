<script setup lang="ts">
    import { computed } from 'vue';
    import {
        DialogClose,
        DialogContent,
        DialogDescription,
        DialogOverlay,
        DialogPortal,
        DialogRoot,
        DialogTitle,
    } from 'radix-vue'
    import { Icon } from '@iconify/vue'

    const props = withDefaults(
        defineProps<{
            title?: string;
            open?: boolean;
            maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
            closeable?: boolean;
        }>(),
        {
            title: '',
            open: false,
            maxWidth: '2xl',
            closeable: true,
        }
    );

    const emit = defineEmits(['close']);

    const close = () => {
        if (props.closeable) {
            emit('close');
        }
    };

    const maxWidthClass = computed(() => {
        return {
            sm: 'sm:max-w-sm',
            md: 'sm:max-w-md',
            lg: 'sm:max-w-lg',
            xl: 'sm:max-w-xl',
            '2xl': 'sm:max-w-2xl',
        }[props.maxWidth];
    });
</script>

<template>
    <DialogRoot :open="open">
        <DialogPortal>
            <DialogOverlay class="backdrop-blur-sm bg-black/20 data-[state=open]:animate-overlayShow data-[state=close]:animate-overlayHide fixed inset-0 z-30" />
            <DialogContent
                class="data-[state=open]:animate-contentShow overflow-auto data-[state=close]:animate-contentHide fixed top-[50%] left-[50%] max-h-[85vh] w-full max-w-md translate-x-[-50%] translate-y-[-50%] rounded-md bg-secondary-light dark:bg-secondary-dark p-[20px] shadow-[0px_10px_38px_-10px_rgba(22,_23,_24,_0.35),_0px_10px_20px_-15px_rgba(22,_23,_24,_0.2)] shadow-black dark:shadow-white focus:outline-none z-[100]"
                :class="maxWidthClass"
                @escapeKeyDown="close"
                @pointerDownOutside="close"
            >
                <DialogTitle v-if="title" class="text-zero-light dark:text-zero-dark m-0 text-[20px] font-semibold">
                    {{ title }}
                </DialogTitle>
                <DialogDescription class="mt-[10px] mb-5 text-[15px] leading-normal">
                    <slot />
                </DialogDescription>
                <slot name="buttons" />
                <DialogClose
                    class="text-secondary-light dark:text-secondary-dark hover:bg-secondary-light-hover dark:hover:bg-secondary-dark-hover absolute top-[10px] right-[10px] inline-flex h-[25px] w-[25px] appearance-none items-center justify-center rounded-full focus:shadow-[0_0_0_2px] focus:outline-none"
                    aria-label="Close"
                    @click="close"
                >
                    <Icon icon="mdi:window-close" />
                </DialogClose>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>