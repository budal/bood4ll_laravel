<script setup lang="ts">
  import { Icon } from '@iconify/vue'
  import Button from '../Button.vue';
  import Modal from '../Modal.vue';
  import { trans } from 'laravel-vue-i18n';
  import { toast } from 'vue3-toastify';
  import { useForm, usePage } from '@inertiajs/vue3';
  import { ref } from 'vue'

  const props = defineProps<{
    item: any;
    restoreRoute: string;
  }>();

  const form = useForm({
    ids: [],
  });

  const confirmRestoreModal = ref(false);
  const restoreItemID = ref('');

  const restore = (id: string) => {
    confirmRestoreModal.value = true;
    restoreItemID.value = id;
  }

  const restoreItem = () => {
    form.post(route(props.restoreRoute, restoreItemID.value), {
      preserveScroll: true,
      onSuccess: () => closeRestoreModal(),
      onError: () => toast.error(trans(usePage().props.status as string)),
      onFinish: () => toast.success(trans(usePage().props.status as string)),
    });
  };

  const closeRestoreModal = () => confirmRestoreModal.value = false
</script>

<template>
  <Modal 
    :open="confirmRestoreModal" 
    :title="$t('Are you sure you want to restore this item?')" 
    @close="closeRestoreModal"
  >
    <p class="mt-1 text-sm text-secondary-light dark:text-secondary-dark">
      {{ $t('The selected item will be restored to the active items. Do you want to continue?') }}
    </p>
    <template #buttons>
      <div class="mt-6 flex justify-end">
        <Button color="secondary" @click="closeRestoreModal" start-icon="mdi:cancel-outline">
          {{ $t('Cancel') }}
        </Button>
        <Button color="success" @click="restoreItem" start-icon="mdi:backup-restore" class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ $t('Restore') }}
        </Button>
      </div>
    </template>
  </Modal>

  <Button v-if="restoreRoute && item.deleted_at" 
    type="button"
    color="warning" padding="2" 
    @click="restore(item.id)"
  >
    <Icon icon="mdi:restore" class="h-5 w-5" />
  </Button>
</template>
