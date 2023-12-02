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
            theme?: 'zero' | 'primary' | 'secondary' | 'danger' | 'success' | 'warning' | 'info' ;
            title?: string;
            subTitle?: string;
            open?: boolean;
            maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
            closeable?: boolean;
        }>(),
        {
            title: '',
            subtitle: '',
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
                class="data-[state=open]:animate-contentShow data-[state=close]:animate-contentHide fixed top-[50%] left-[50%] max-h-[85vh] w-full max-w-md translate-x-[-50%] translate-y-[-50%] rounded-md p-[20px] shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus:outline-none z-[100]"
                :class="`${maxWidthClass} bg-${theme}-light-hover dark:bg-${theme}-dark-hover`"
                @escapeKeyDown="close"
                @pointerDownOutside="close"
            >
                <DialogTitle v-if="title" :class="`text-${theme}-light dark:text-${theme}-dark m-0 text-[20px] font-semibold`">
                    {{ title }}
                </DialogTitle>
                <DialogDescription class="mt-[10px] mb-5 text-[15px] leading-normal">
                    <p  v-if="subTitle" :class="`mt-1 text-sm text-${theme}-light/70 dark:text-${theme}-dark/70`">
                    {{ $t(subTitle) }}
                    </p>
                    <slot />
                </DialogDescription>
                <slot name="buttons" />
                <DialogClose
                    class="absolute top-[10px] right-[10px] inline-flex h-[25px] w-[25px] appearance-none items-center justify-center rounded-full focus:shadow-[0_0_0_2px] focus:outline-none"
                    :class="`text-${theme}-light dark:text-${theme}-dark hover:bg-${theme}-light-hover dark:hover:bg-${theme}-dark-hover`"
                    aria-label="Close"
                    @click="close"
                >
                    <Icon icon="mdi:window-close" />
                </DialogClose>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>