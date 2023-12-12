<script setup lang="ts">
import { Icon } from "@iconify/vue";
import {
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
    DialogTitle,
} from "radix-vue";
import { transChoice } from "laravel-vue-i18n";
import { computed } from "vue";

const props = withDefaults(
    defineProps<{
        theme?:
            | "zero"
            | "primary"
            | "secondary"
            | "danger"
            | "success"
            | "warning"
            | "info";
        title?: string;
        subTitle?: string;
        items?: number;
        open?: boolean;
        maxWidth?:
            | "sm"
            | "md"
            | "lg"
            | "xl"
            | "2xl"
            | "3xl"
            | "4xl"
            | "5xl"
            | "6xl"
            | "7xl";
        closeable?: boolean;
    }>(),
    {
        theme: "zero",
        title: "",
        subtitle: "",
        items: 0,
        open: false,
        maxWidth: "2xl",
        closeable: true,
    },
);

const emit = defineEmits(["close"]);

const close = () => {
    if (props.closeable) {
        emit("close");
    }
};

const maxWidthClass = computed(() => {
    return {
        sm: "sm:max-w-sm",
        md: "sm:max-w-md",
        lg: "sm:max-w-lg",
        xl: "sm:max-w-xl",
        "2xl": "sm:max-w-2xl",
        "3xl": "sm:max-w-3xl",
        "4xl": "sm:max-w-4xl",
        "5xl": "sm:max-w-5xl",
        "6xl": "sm:max-w-6xl",
        "7xl": "sm:max-w-7xl",
    }[props.maxWidth];
});
</script>

<template>
    <DialogRoot :open="open">
        <DialogPortal>
            <DialogOverlay
                class="backdrop-blur-sm bg-black/20 data-[state=open]:animate-overlayShow data-[state=close]:animate-overlayHide fixed inset-0 z-30"
            />
            <DialogContent
                class="data-[state=open]:animate-contentShow data-[state=close]:animate-contentHide fixed top-[50%] left-[50%] max-h-[85vh] w-full max-w-md translate-x-[-50%] translate-y-[-50%] rounded-md p-2 sm:p-[20px] shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] focus:outline-none z-[100]"
                :class="`${maxWidthClass} bg-${theme}-light-hover dark:bg-${theme}-dark-hover`"
                @escapeKeyDown="close"
                @pointerDownOutside="close"
            >
                <DialogTitle
                    v-if="title"
                    :class="`text-${theme}-light dark:text-${theme}-dark m-0 text-[20px] font-semibold`"
                >
                    {{ transChoice(title, items) }}
                </DialogTitle>
                <DialogDescription
                    class="mt-[10px] mb-5 text-[15px] leading-normal"
                >
                    <p
                        v-if="subTitle"
                        :class="`mt-1 text-sm text-${theme}-light/70 dark:text-${theme}-dark/70`"
                    >
                        {{ transChoice(subTitle, items) }}
                    </p>
                    <slot />
                </DialogDescription>
                <div class="mt-6 flex justify-end">
                    <slot name="buttons" />
                </div>
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
