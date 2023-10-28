<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-info-light dark:border-info-dark text-sm font-medium leading-5 text-primary-dark dark:text-primary-light focus:outline-none focus:border-warning-light dark:focus:border-warning-dark transition duration-300 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-primary-dark/80 dark:text-primary-light/80 hover:text-primary-dark dark:hover:text-primary-light hover:border-primary-dark dark:hover:border-primary-light focus:outline-none focus:text-primary-dark dark:focus:text-primary-light focus:border-primary-dark dark:focus:border-primary-light transition duration-300 ease-in-out'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
