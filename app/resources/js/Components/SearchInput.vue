<script setup lang="ts">
import { Icon } from "@iconify/vue";
import { onBeforeMount } from "vue";
import { useAttrs } from "vue";
import { computed, onBeforeUnmount, ref, watch } from "vue";

const props = withDefaults(
    defineProps<{
        id?: string;
        name?: string;
        value?: string;
        modelValue: Object | string | number;
        placeholder?: string | undefined;
        shortcutIcon?: boolean;
        clearIcon?: boolean;
        hideShortcutIconOnBlur?: boolean;
        clearOnEsc?: boolean;
        blurOnEsc?: boolean;
        selectOnFocus?: boolean;
        shortcutListenerEnabled?: boolean;
        shortcutKey?: string;
    }>(),
    {
        shortcutIcon: true,
        clearIcon: true,
        hideShortcutIconOnBlur: false,
        clearOnEsc: true,
        blurOnEsc: true,
        selectOnFocus: true,
        shortcutListenerEnabled: true,
        shortcutKey: "/",
    },
);

const randomName = (Math.random() + 1).toString(36).substring(7);

const emit = defineEmits(["update:modelValue"]);

const attrs = useAttrs();

const hasFocus = ref(false);

const inputRef = ref<null | HTMLInputElement>(null);

const showClearIcon = computed(() => {
    if (props.clearIcon && hasFocus.value) return true;

    return false;
});

const showShortcutIcon = computed(() => {
    if (props.shortcutIcon && !hasFocus.value && !props.hideShortcutIconOnBlur)
        return true;

    return false;
});

const clear = () => {
    emit("update:modelValue", "");
};

const onKeypress = (e: KeyboardEvent) => {
    emit("update:modelValue", inputRef.value?.value);
};

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === "Escape") {
        props.clearOnEsc && clear();
        if (props.blurOnEsc) {
            const el = inputRef.value as HTMLInputElement;
            el.blur();
        }
    }
};

const onDocumentKeydown = (e: KeyboardEvent) => {
    if (
        e.key === props.shortcutKey &&
        e.target !== inputRef.value &&
        window.document.activeElement !== inputRef.value &&
        e.target instanceof HTMLInputElement === false &&
        e.target instanceof HTMLSelectElement === false &&
        e.target instanceof HTMLTextAreaElement === false
    ) {
        e.preventDefault();
        const allVisibleSearchInputs = [].slice
            .call(
                document.querySelectorAll(
                    '[data-search-input="true"]:not([data-shortcut-enabled="false"])',
                ),
            )
            .filter((el: HTMLElement) => {
                return !!(
                    el.offsetWidth ||
                    el.offsetHeight ||
                    el.getClientRects().length
                );
            });
        const elToFocus =
            allVisibleSearchInputs.length > 1
                ? allVisibleSearchInputs[0]
                : inputRef.value;

        elToFocus?.focus();
        if (props.selectOnFocus) elToFocus?.select();
    }
};

const removeDocumentKeydown = () =>
    window.document.removeEventListener("keydown", onDocumentKeydown);

watch(
    () => props.shortcutListenerEnabled,
    (nV) => {
        if (nV) {
            window.document.addEventListener("keydown", onDocumentKeydown);
        } else {
            removeDocumentKeydown();
        }
    },
    {
        immediate: true,
    },
);

onBeforeMount(() => {
    emit("update:modelValue", props.modelValue ?? "");
});

onBeforeUnmount(() => {
    removeDocumentKeydown();
});
</script>

<template>
    <div class="flex">
        <div class="relative" v-bind="attrs">
            <div
                class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none"
            >
                <Icon
                    icon="mdi:search"
                    class="w-6 h-6 text-primary-light dark:text-primary-dark"
                />
            </div>
            <input
                class="w-full block p-2 pl-9 placeholder:text-sm placeholder-primary-white/20 dark:placeholder-primary-dark/20 bg-zero-white dark:bg-zero-black text-zero-light dark:text-zero-dark rounded-md border border-zero-light dark:border-zero-dark focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark dark:focus-within:shadow-primary-dark transition ease-in-out duration-500 disabled:opacity-25"
                :placeholder="placeholder"
                ref="inputRef"
                type="search"
                data-search-input="true"
                :data-shortcut-enabled="shortcutListenerEnabled"
                :id="name || randomName"
                :name="name || randomName"
                :value="modelValue"
                @focus="hasFocus = true"
                @blur="hasFocus = false"
                @keyup="onKeypress"
                @keydown="onKeydown"
            />
            <span
                v-if="showClearIcon"
                @click="clear"
                class="text-zero-light dark:text-zero-dark absolute inset-y-2 right-2 px-2 rounded-lg text-xs p-1 bg-zero-light dark:bg-zero-dark ring-0"
                >{{ $t("'Esc' to clear") }}</span
            >
            <span
                v-if="showShortcutIcon"
                class="text-zero-light dark:text-zero-dark absolute inset-y-2 right-2 px-2 rounded-lg text-sm p-1 bg-zero-light dark:bg-zero-dark ring-0"
                >{{ shortcutKey }}</span
            >
        </div>
    </div>
</template>
