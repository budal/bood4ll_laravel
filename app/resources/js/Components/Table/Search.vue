<script setup lang="ts">
  import SearchInput from '@/Components/SearchInput.vue';
  import debounce from "lodash.debounce";
  import { router } from '@inertiajs/vue3';
  import { ref, watch, onBeforeUnmount } from 'vue'

  defineProps<{
    id?: string;
    name?: string;
    shortcutKey?: string;
    search: any;
  }>();

  const searchRoute = new URL(window.location.href);

  const search = ref('');

  const debouncedWatch = debounce(() => {
    searchRoute.searchParams.set("search", search.value)

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
    :search="search" 
    :shortcutKey="shortcutKey"
    :placeholder="$t('Search...')" 
    class="w-full h-full" 
    />
</template>
