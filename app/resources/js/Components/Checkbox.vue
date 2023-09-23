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
        class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-500 text-gray-600 dark:text-gray-600 shadow-sm focus:ring-gray-600 dark:focus:ring-gray-500 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-500 shadow-sm"
    />
</template>
