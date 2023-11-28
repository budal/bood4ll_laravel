<script setup lang="ts">
  import { Icon } from '@iconify/vue'
  import { Link } from '@inertiajs/vue3';

  const props = defineProps<{
    prefix?: string | null;
    sort: any;
  }>();

  const sortedFieldName = props.prefix ? `${props.prefix}_sorted` : "sorted"

  const sortBy = (column: any) => {
    let url = new URL(window.location.href);
    let sortOrder = null;
    let sortValue = url.searchParams.get(sortedFieldName)

    if (sortValue == column) {
      url.searchParams.set(sortedFieldName, "-" + column)
      sortOrder = "asc"
    } else if (sortValue === "-" + column) {
      url.searchParams.set(sortedFieldName, column)
      sortOrder = "desc"
    } else {
      url.searchParams.set(sortedFieldName, column)
    }
    
    return {
      url: url.href,
      sortMe: sortOrder,
    }
  }
</script>

<template>
  <Link 
    v-if="sort.disableSort != true" 
    :href="sortBy(sort.field).url" 
    class="group focus:outline-none flex gap-1 items-center"
    preserve-scroll
  >
    <span class="border-b-2 border-transparent group-hover:border-zero-light dark:group-hover:border-zero-dark group-focus:border-zero-light dark:group-focus:border-zero-dark transition ease-in-out duration-500">
      {{ $t(sort.title) }}
    </span>
    <Icon icon="mdi:chevron-up-circle-outline" v-if="sortBy(sort.field).sortMe == 'asc'" class="h-4 w-4" />
    <Icon icon="mdi:chevron-down-circle-outline" v-if="sortBy(sort.field).sortMe == 'desc'" class="h-4 w-4" />
  </Link>
</template>
