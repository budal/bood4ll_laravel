<script setup lang="ts">
import { ref } from "vue";
import { SwitchRoot, SwitchThumb } from "radix-vue";

const props = defineProps<{
    checked: boolean;
    value?: any;
}>();

const emit = defineEmits(["update:checked"]);

const proxyChecked = ref(props.checked);
</script>

<template>
    <div class="flex">
        <div
            class="shrink rounded-full border-2 border-transparent hover:border-zero-light dark:hover:border-zero-dark focus-within:border-zero-light dark:focus-within:border-zero-dark hover:outline hover:outline-2 hover:outline-primary-light dark:hover:outline-primary-dark hover:outline-offset-2 hover:outline-offset-primary-light dark:hover:outline-offset-primary-dark focus-within:ring-2 focus-within:ring-primary-light dark:focus-within:ring-primary-dark focus-within:ring-offset-2 focus-within:ring-offset-primary-light dark:focus-within:ring-offset-primary-dark transition ease-in-out duration-500"
        >
            <SwitchRoot
                v-model:checked="proxyChecked"
                class="w-[42px] h-[25px] focus:outline-none flex bg-primary-light/80 dark:bg-primary-dark/70 rounded-full data-[state=checked]:bg-primary-light data-[state=checked]:dark:bg-primary-dark cursor-pointer"
            >
                <span class="sr-only">{{ value }}</span>
                <SwitchThumb
                    class="block w-[21px] h-[21px] my-auto rounded-full transition-transform duration-300 translate-x-0.5 will-change-transform data-[state=checked]:translate-x-[19px]"
                    :class="'bg-primary-dark dark:bg-primary-light'"
                />
            </SwitchRoot>
        </div>
    </div>
</template>
