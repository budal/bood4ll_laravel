<script setup lang="ts">
import { isValidUrl } from "@/helpers";
import { Icon } from "@iconify/vue";

const props = withDefaults(
    defineProps<{
        values: any;
        color?:
            | "zero"
            | "primary"
            | "secondary"
            | "danger"
            | "success"
            | "warning"
            | "info";
        textSize?:
            | "text-xs"
            | "text-sm"
            | "text-base"
            | "text-lg"
            | "text-xl"
            | "text-2xl"
            | "text-3xl"
            | "text-4xl"
            | "text-5xl"
            | "text-6xl"
            | "text-7xl"
            | "text-8xl"
            | "text-9xl";
        transform?:
            | "uppercase"
            | "lowercase"
            | "lowercase"
            | "capitalize"
            | "capitalize"
            | "normal-case";
        startIcon?: string;
        endIcon?: string;
        title?: string;
        link?:
            | {
                  route: string | [];
                  attributes: [];
              }
            | string;
    }>(),
    {
        color: "primary",
        textSize: "text-xs",
        transform: "uppercase",
    },
);
</script>

<template>
    <div class="grid grid-flow-col justify-stretch gap-2 rounded-lg text-xs">
        <template v-for="link in values">
            <a
                v-if="link.showIf !== false"
                :href="isValidUrl(link.route)"
                :method="link.method || 'get'"
                as="button"
                type="button"
                class="hover:scale-105 h-14 rounded-lg flex items-center justify-center shadow-lg bg-primary-light dark:bg-primary-dark hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover text-primary-light dark:text-primary-dark focus:outline-none border-transparent transition ease-in-out duration-500"
            >
                <div class="flex gap-1 items-center">
                    <Icon
                        v-if="link.startIcon"
                        :icon="link.startIcon"
                        class="h-5 w-5"
                    />
                    <span v-if="link.title">{{ $t(link.title) }}</span>
                    <Icon
                        v-if="link.endIcon"
                        :icon="link.endIcon"
                        class="h-5 w-5"
                    />
                </div>
            </a>
        </template>
    </div>
</template>
