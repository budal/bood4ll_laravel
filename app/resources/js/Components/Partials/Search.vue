<script setup lang="ts">
  import SearchInput from '../SearchInput.vue';
  import debounce from "lodash.debounce";
  import { router, useForm, usePage, Link } from '@inertiajs/vue3';
  import { ref, computed, reactive, watch, onBeforeUnmount } from 'vue'

  const props = defineProps<{
    value: any;
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
  <SearchInput :placeholder="$t('Search...')" id="search" name="search" class="w-full h-full" :value="value" v-model="search" />
</template>
