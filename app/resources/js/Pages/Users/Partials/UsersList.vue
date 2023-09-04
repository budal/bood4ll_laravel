<script setup lang="ts">
import Table from '@/Components/Table.vue';

defineProps<{
    items?: object;
}>();
</script>

<template>
  <section>
      <header>
          <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Users</h2>

          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              Manage users informations and authorizations.
          </p>
      </header>

      <Table :items="items" />
  </section>
</template>