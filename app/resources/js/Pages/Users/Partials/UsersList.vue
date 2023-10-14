<script setup lang="ts">
import Table from '@/Components/Table.vue';

defineProps<{
    status: any;
    filters: any;
    hideFilters?: any;
    items: any;
    titles: any;
    indexRoute: string;
    createRoute?: string;
    storeRoute?: string;
    editRoute?: string;
    updateRoute?: string;
    destroyRoute?: string;
    restoreRoute?: string;
}>();
</script>

<template>
  <section>
      <header>
          <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $t('Users management') }}</h2>

          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              {{ $t('Manage users informations and authorizations.') }}
          </p>
      </header>

      <Table 
        :status="status" 
        :filters="filters" 
        :hideFilters="hideFilters" 
        :items="items" 
        :titles="titles" 
        :indexRoute="indexRoute" 
        :createRoute="createRoute" 
        :editRoute="editRoute" 
        :destroyRoute="destroyRoute" 
        :restoreRoute="restoreRoute" 
    />
  </section>
</template>