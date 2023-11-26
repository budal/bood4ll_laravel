<script setup lang="ts">
  import Button from '@/Components/Button.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import Modal from '@/Components/Modal.vue';
  import Select from '@/Components/Select.vue';
  import { router } from '@inertiajs/vue3';
  import { ref } from 'vue'

  const props = defineProps<{
    softDelete?: boolean | null;
  }>();

  const searchRoute = new URL(window.location.href);

  const filterContent = [
    { id: 'active', name: 'Only active', disabled: false },
    { id: 'trashed', name: 'Only trashed', disabled: props.softDelete === true ? false : true },
    { id: 'both', name: 'Active and trashed', disabled: props.softDelete === true ? false : true },
  ]

  const filterContentValue = ref(searchRoute.searchParams.get("trashed") || 'active')

  const filtersModal = ref(false);

  const openFiltersModal = () => filtersModal.value = true

  const refreshFilters = () => {
    searchRoute.searchParams.set("trashed", filterContentValue.value)

    router.visit(searchRoute, {
      method: 'get',
      preserveScroll: true,
      onSuccess: () => closeFiltersModal(),
    })
  };

  const closeFiltersModal = () => filtersModal.value = false

</script>

<template>
  <Modal 
    :open="filtersModal" 
    :title="$t('Manage which filters to apply to the list')" 
    @close="closeFiltersModal"
  >
    <p class="mt-1 text-sm text-secondary-light dark:text-secondary-dark">
      {{ $t('Selected filters refine searches according to your choices') }}
    </p>
    <div class="pt-3">
      <InputLabel for="filterContent" :value="$t('Content')" />
      <Select 
        id="filterContent" 
        name="filterContent" 
        :content="filterContent" 
        class="mt-1" 
        v-model="filterContentValue" 
        :disableSearch="true"
      />
    </div>
    <template #buttons>
      <div class="mt-6 flex justify-end">
        <Button color="secondary" type="button" @click="closeFiltersModal" start-icon="mdi:cancel-outline">
          {{ $t('Cancel') }}
        </Button>
        <Button color="primary" type="button" @click="refreshFilters" start-icon="mdi:check-outline" class="ml-3">
          {{ $t('Apply') }}
        </Button>
      </div>
    </template>
  </Modal>

  <Button color="secondary" type="button" @click="openFiltersModal" class="ml-2 h-full" start-icon="mdi:filter-settings-outline" />
</template>
