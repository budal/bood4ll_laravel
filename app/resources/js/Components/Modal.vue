<script setup lang="ts">
import { computed } from 'vue';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

const props = withDefaults(
    defineProps<{
        title?: string;
        show?: boolean;
        maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
        closeable?: boolean;
    }>(),
    {
        title: '',
        show: false,
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
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" @close="close" class="relative z-[100]">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div :show="show" class="fixed inset-0 backdrop-blur-sm bg-black/20" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto no-scrollbar w-screen">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel :class="`${maxWidthClass} w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl sm:w-full sm:mx-auto transition-all`">
                            <DialogTitle v-if="title" as="h3" class="text-lg font-medium text-gray-900 dark:text-gray-100 leading-6">
                                {{ title }}
                            </DialogTitle>
                            <slot />
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>