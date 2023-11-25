<script setup lang="ts">
  import { Icon } from '@iconify/vue'
  import Avatar from '../Avatar.vue';
  import Button from '../Button.vue';
  import Checkbox from '../Checkbox.vue';
  import InputLabel from '../InputLabel.vue';
  import Modal from '../Modal.vue';
  import Select from '../Select.vue';
  import Switch from '../Switch.vue';
  import SearchInput from '../SearchInput.vue';
  import {
    DropdownMenuArrow,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuPortal,
    DropdownMenuRoot,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
  } from 'radix-vue'
  import debounce from "lodash.debounce";
  import { trans } from 'laravel-vue-i18n';
  import { toast } from 'vue3-toastify';
  import { router, useForm, usePage, Link } from '@inertiajs/vue3';
  import { ref, computed, reactive, watch, onBeforeUnmount } from 'vue'

  const props = defineProps<{
    id?: string;
    name?: string;
    apiRoute?: string;
    softDelete?: boolean | null;
    routes?: any;
    filters?: any;
    menu?: any;
    titles: any;
    items: any;

    route: string;
  }>();

  const filterContent = [
    { id: 'active', name: 'Only active', disabled: false },
    { id: 'trashed', name: 'Only trashed', disabled: props.softDelete === true ? false : true },
    { id: 'both', name: 'Active and trashed', disabled: props.softDelete === true ? false : true },
  ]

  const filterContentValue = ref(searchRoute.searchParams.get("trashed") || '')

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
    :open="confirmingDeletionModal"
    :title="$t('Are you sure you want to delete the selected items?')" 
    @close="closeDeletionModal"
  >
    <p class="mt-1 text-sm text-secondary-light dark:text-secondary-dark">
      {{ $t('The selected items will be removed from the active items. Do you want to continue?') }}
    </p>
    <template #buttons>
      <div class="mt-6 flex justify-end">
        <Button color="secondary" @click="closeDeletionModal" start-icon="mdi:cancel-outline">
          {{ $t('Cancel') }}
        </Button>
        <Button color="danger" @click="deleteItems" start-icon="mdi:delete-sweep-outline" class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ $t('Erase selected') }}
        </Button>
      </div>
    </template>
  </Modal>
</template>
