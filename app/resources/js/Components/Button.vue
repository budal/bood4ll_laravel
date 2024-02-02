<script setup lang="ts">
import { isValidUrl, toast } from "@/helpers";
import { Icon } from "@iconify/vue";
import { router } from "@inertiajs/vue3";

const props = withDefaults(
    defineProps<{
        type?: "button" | "submit" | "reset";
        color?:
            | "zero"
            | "primary"
            | "secondary"
            | "danger"
            | "success"
            | "warning"
            | "info";
        padding?: number | string;
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
        preserveState?: boolean;
        preserveScroll?: boolean;
        method?: "get" | "post" | "patch" | "put" | "delete";
        srOnly?: string;
        title?: string;
        menu?: object;
        link?:
            | {
                  route: string | [];
                  attributes: [];
              }
            | string;
    }>(),
    {
        type: "submit",
        color: "primary",
        padding: 4,
        textSize: "text-xs",
        transform: "uppercase",
        preserveState: false,
        preserveScroll: false,
        method: "get",
    },
);

const onClick = () => {
    if (props.link) {
        let indexRoute = new URL(window.location.href);
        const __tab = indexRoute.searchParams.get("__tab");

        const route = new URL(isValidUrl(props.link));
        __tab ? route.searchParams.set("__tab", __tab) : false;

        router.visit(route, {
            method: props.method,
            preserveState: props.preserveState,
            preserveScroll: props.preserveScroll,
            onSuccess: () => toast(),
        });
    }
};
</script>

<template>
    <button
        @click="onClick"
        :type="type"
        :class="`hover:scale-105 min-h-[41px] inline-flex items-center px-${padding} bg-${color}-light dark:bg-${color}-dark hover:bg-${color}-light-hover dark:hover:bg-${color}-dark-hover border border-${color}-light dark:border-${color}-dark rounded-md font-semibold ${textSize} text-${color}-light dark:text-${color}-dark ${transform} tracking-widest focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-2 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark disabled:opacity-25 transition ease-in-out duration-500`"
    >
        <div class="flex gap-1 items-center">
            <Icon v-if="startIcon" :icon="startIcon" class="h-5 w-5" />
            <slot />
            <span v-if="srOnly" class="sr-only">{{ srOnly }}</span>
            <span v-if="title">{{ $t(title) }}</span>
            <Icon v-if="endIcon" :icon="endIcon" class="h-5 w-5" />
        </div>
    </button>
</template>
