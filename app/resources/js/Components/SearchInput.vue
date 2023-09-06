<script lang="ts">
import { PropType, computed, defineComponent, onBeforeUnmount, ref, watch } from 'vue'

export const fieldType = ['search', 'text'] as const
export type FieldType = (typeof fieldType)[number]

const filterObject = (obj: { [key: string]: unknown }, properties: (string | number)[], remove = true) => {
  const res: { [key: string]: unknown } = {}

  Object.keys(obj).forEach((objAttr) => {
    const condition = remove ? properties.indexOf(objAttr) === -1 : properties.indexOf(objAttr) >= 0

    if (condition) {
      res[objAttr] = obj[objAttr]
    }
  })

  return res
}

const defaultBoolean = (val = true) => ({ type: Boolean, default: val })

export default defineComponent({
  inheritAttrs: false,
  props: {
    type: {
      type: String as PropType<FieldType>,
      default: 'search',
      validator: (prop: FieldType) => fieldType.includes(prop)
    },
    placeholder: {
      type: String,
      default: ''
    },
    modelValue: {
      type: String,
      default: ''
    },
    searchIcon: defaultBoolean(),
    shortcutIcon: defaultBoolean(),
    clearIcon: defaultBoolean(),
    hideShortcutIconOnBlur: defaultBoolean(),
    clearOnEsc: defaultBoolean(),
    blurOnEsc: defaultBoolean(),
    selectOnFocus: defaultBoolean(),
    shortcutListenerEnabled: defaultBoolean(),
    shortcutKey: {
      type: String as PropType<KeyboardEvent['key']>,
      default: '/'
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit, attrs }) {
    const hasFocus = ref(false)
    const inputRef = ref<null | HTMLInputElement>(null)

    const attrsStyles = computed(() => {
      const res = filterObject(attrs, ['class', 'style'], false)

      return res
    })

    const showClearIcon = computed(() => !!(props.clearIcon && props.modelValue.length > 0))

    const showShortcutIcon = computed(() => {
      if (props.shortcutIcon && !hasFocus.value && !props.hideShortcutIconOnBlur) return true
      if (props.shortcutIcon && !hasFocus.value && props.modelValue.length === 0) return true

      return false
    })

    const clear = () => {
      emit('update:modelValue', '')
    }

    const onInput = (e: Event) => {
      emit('update:modelValue', (e.target as HTMLInputElement).value)
    }

    const onKeydown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        props.clearOnEsc && clear()
        if (props.blurOnEsc) {
          const el = inputRef.value as HTMLInputElement

          el.blur()
        }
      }
    }

    const onDocumentKeydown = (e: KeyboardEvent) => {
      if (
        e.key === props.shortcutKey &&
        e.target !== inputRef.value &&
        window.document.activeElement !== inputRef.value &&
        e.target instanceof HTMLInputElement === false &&
        e.target instanceof HTMLSelectElement === false &&
        e.target instanceof HTMLTextAreaElement === false
      ) {
        e.preventDefault()
        const allVisibleSearchInputs = [].slice
          .call(document.querySelectorAll('[data-search-input="true"]:not([data-shortcut-enabled="false"])'))
          .filter((el: HTMLElement) => {
            return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length)
          })
        const elToFocus = allVisibleSearchInputs.length > 1 ? allVisibleSearchInputs[0] : inputRef.value

        elToFocus?.focus()
        if (props.selectOnFocus) elToFocus?.select()
      }
    }

    const removeDocumentKeydown = () => window.document.removeEventListener('keydown', onDocumentKeydown)

    watch(
      () => props.shortcutListenerEnabled,
      (nV) => {
        if (nV) {
          window.document.addEventListener('keydown', onDocumentKeydown)
        } else {
          removeDocumentKeydown()
        }
      },
      { immediate: true }
    )

    onBeforeUnmount(() => {
      removeDocumentKeydown()
    })

    return {
      inputRef,
      hasFocus,
      clear,
      onInput,
      onKeydown,
      attrsStyles,
      showClearIcon,
      showShortcutIcon
    }
  }
})
</script>

<template>
    <div class="flex sticky top-0">
        <div class="relative" v-bind="attrsStyles">
            <div v-if="searchIcon" name="search-icon" class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
            </div>
            <input
                class="w-full block p-2 pl-9 placeholder:text-sm dark:placeholder-gray-400 dark:bg-gray-900 dark:text-gray-300 rounded-md border-gray-300 dark:border-gray-700 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm" 
                :placeholder=placeholder
                ref="inputRef"
                type="search"
                data-search-input="true"
                :data-shortcut-enabled="shortcutListenerEnabled"
                :value="modelValue"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                @focus="hasFocus = true"
                @blur="hasFocus = false"
                @keydown="onKeydown"
            />
            <button v-if="showClearIcon" class="dark:text-gray-300 absolute inset-y-2 right-2 px-2 rounded-lg text-xs p-1 bg-gray-200 dark:bg-gray-700 ring-0" aria-label="Clear" @mousedown="clear" @keydown.space.enter="clear">Esc</button>
            <span v-if="showShortcutIcon" class="dark:text-gray-300 absolute inset-y-2 right-2 px-2 rounded-lg text-sm p-1 bg-gray-200 dark:bg-gray-700 ring-0">{{ shortcutKey }}</span>
        </div>
    </div>
</template>