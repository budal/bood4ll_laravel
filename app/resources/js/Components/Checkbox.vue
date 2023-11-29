<script setup lang="ts">
    import { computed } from 'vue';

    const emit = defineEmits(['update:checked']);

    const props = defineProps<{
        checked: boolean;
        value?: any;
    }>();

    const proxyChecked = computed({
        get() {
            return props.checked;
        },

        set(val) {
            emit('update:checked', val);
        },
    });
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded bg-zero-light dark:bg-zero-dark text-zero-light dark:text-zero-dark border-zero-light dark:border-zero-dark focus:ring-zero-light dark:focus:ring-zero-dark focus:ring-offset-zero-light dark:focus:ring-offset-zero-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] disabled:opacity-25 transition ease-in-out duration-500"
    />
</template>
