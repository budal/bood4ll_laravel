<script setup lang="ts">
import { computed } from 'vue';

    const props = withDefaults(
        defineProps<{
            type?: 'button' | 'submit' | 'reset';
            color?: 'primary' | 'secondary' | 'danger' | 'success' | 'warning' | 'info';
            padding?: number | string;
            rounded?: 'rounded-none' | 'rounded-sm' | 'rounded' | 'rounded-md' | 'rounded-lg' | 'rounded-xl' | 'rounded-2xl' | 'rounded-3xl' | 'rounded-full' | 'rounded-s-none' | 'rounded-s-sm' | 'rounded-s' | 'rounded-s-md' | 'rounded-s-lg' | 'rounded-s-xl' | 'rounded-s-2xl' | 'rounded-s-3xl' | 'rounded-s-full' | 'rounded-e-none' | 'rounded-e-sm' | 'rounded-e' | 'rounded-e-md' | 'rounded-e-lg' | 'rounded-e-xl' | 'rounded-e-2xl' | 'rounded-e-3xl' | 'rounded-e-full' | 'rounded-t-none' | 'rounded-t-sm' | 'rounded-t' | 'rounded-t-md' | 'rounded-t-lg' | 'rounded-t-xl' | 'rounded-t-2xl' | 'rounded-t-3xl' | 'rounded-t-full' | 'rounded-r-none' | 'rounded-r-sm' | 'rounded-r' | 'rounded-r-md' | 'rounded-r-lg' | 'rounded-r-xl' | 'rounded-r-2xl' | 'rounded-r-3xl' | 'rounded-r-full' | 'rounded-b-none' | 'rounded-b-sm' | 'rounded-b' | 'rounded-b-md' | 'rounded-b-lg' | 'rounded-b-xl' | 'rounded-b-2xl' | 'rounded-b-3xl' | 'rounded-b-full' | 'rounded-l-none' | 'rounded-l-sm' | 'rounded-l' | 'rounded-l-md' | 'rounded-l-lg' | 'rounded-l-xl' | 'rounded-l-2xl' | 'rounded-l-3xl' | 'rounded-l-full' | 'rounded-ss-none' | 'rounded-ss-sm' | 'rounded-ss' | 'rounded-ss-md' | 'rounded-ss-lg' | 'rounded-ss-xl' | 'rounded-ss-2xl' | 'rounded-ss-3xl' | 'rounded-ss-full' | 'rounded-se-none' | 'rounded-se-sm' | 'rounded-se' | 'rounded-se-md' | 'rounded-se-lg' | 'rounded-se-xl' | 'rounded-se-2xl' | 'rounded-se-3xl' | 'rounded-se-full' | 'rounded-ee-none' | 'rounded-ee-sm' | 'rounded-ee' | 'rounded-ee-md' | 'rounded-ee-lg' | 'rounded-ee-xl' | 'rounded-ee-2xl' | 'rounded-ee-3xl' | 'rounded-ee-full' | 'rounded-es-none' | 'rounded-es-sm' | 'rounded-es' | 'rounded-es-md' | 'rounded-es-lg' | 'rounded-es-xl' | 'rounded-es-2xl' | 'rounded-es-3xl' | 'rounded-es-full' | 'rounded-tl-none' | 'rounded-tl-sm' | 'rounded-tl' | 'rounded-tl-md' | 'rounded-tl-lg' | 'rounded-tl-xl' | 'rounded-tl-2xl' | 'rounded-tl-3xl' | 'rounded-tl-full' | 'rounded-tr-none' | 'rounded-tr-sm' | 'rounded-tr' | 'rounded-tr-md' | 'rounded-tr-lg' | 'rounded-tr-xl' | 'rounded-tr-2xl' | 'rounded-tr-3xl' | 'rounded-tr-full' | 'rounded-br-none' | 'rounded-br-sm' | 'rounded-br' | 'rounded-br-md' | 'rounded-br-lg' | 'rounded-br-xl' | 'rounded-br-2xl' | 'rounded-br-3xl' | 'rounded-br-full' | 'rounded-bl-none' | 'rounded-bl-sm' | 'rounded-bl' | 'rounded-bl-md' | 'rounded-bl-lg' | 'rounded-bl-xl' | 'rounded-bl-2xl' | 'rounded-bl-3xl' | 'rounded-bl-full'
            textSize?:  'text-xs' | 'text-sm' | 'text-base' | 'text-lg' | 'text-xl' | 'text-2xl' | 'text-3xl' | 'text-4xl' | 'text-5xl' | 'text-6xl' | 'text-7xl' | 'text-8xl' | 'text-9xl';
            transform?: 'uppercase' | 'lowercase' | 'lowercase' | 'capitalize' | 'capitalize' | 'normal-case';
            shadowSize?: 'shadow-sm' | 'shadow' | 'shadow-md' | 'shadow-lg' | 'shadow-xl' | 'shadow-2xl' | 'shadow-inner' | 'shadow-none';
        }>(),
        {
            type: 'submit',
            color: 'primary',
            padding: 4,
            rounded: 'rounded-md',
            textSize: 'text-xs',
            transform: 'uppercase',
            shadowSize: 'shadow-sm',
        }
    );

    const className = computed(() => {
        return `hover:scale-105 group/edit inline-flex items-center px-${props.padding} py-2 bg-${props.color}-light dark:bg-${props.color}-dark hover:bg-${props.color}-light-hover dark:hover:bg-${props.color}-dark-hover border border-${props.color}-light dark:border-${props.color}-dark ${props.rounded} font-semibold ${props.textSize} text-${props.color}-light dark:text-${props.color}-dark ${props.transform} tracking-widest ${props.shadowSize} focus:outline-none focus:ring-2 focus:ring-${props.color}-light dark:focus:ring-${props.color}-dark focus:ring-offset-2 focus:ring-offset-${props.color}-light dark:focus:ring-offset-${props.color}-dark disabled:opacity-25 transition ease-in-out duration-500`;
    })
</script>

<template>
    <button :type="type" :class="className">
        <div class="group-hover/edit:scale-110 transition ease-in-out duration-300">
            <slot />
        </div>
    </button>
</template>
