<script setup lang="ts">
  import SearchInput from '@/Components/SearchInput.vue';
  import debounce from "lodash.debounce";
  import { router } from '@inertiajs/vue3';
  import { ref, watch, onBeforeUnmount } from 'vue'

  const props = defineProps<{
    prefix?: string | null;
    id?: string;
    name?: string;
    shortcutKey?: string;
    search: any;
  }>();

  const searchRoute = new URL(window.location.href);

  const searchFieldName = props.prefix ? `${props.prefix}_search` : "search"

  const value =  searchRoute.searchParams.get(searchFieldName)

  const search = ref(value || '');

  const debouncedWatch = debounce(() => {
    searchRoute.searchParams.set(searchFieldName, search.value)

    router.visit(searchRoute, {
      method: 'get',
      preserveState: true,
      preserveScroll: true,
    })
  }, 500);

  watch(search, debouncedWatch);

  onBeforeUnmount(() => {
    debouncedWatch.cancel();
  })
</script>

<template>
  <SearchInput 
    v-model="search" 
    :id="id" 
    :name="name" 
    :value="search" 
    :shortcutKey="shortcutKey"
    :placeholder="$t('Search...')" 
    class="w-full h-full" 
    />
</template>
