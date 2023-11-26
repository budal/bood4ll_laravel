<script setup lang="ts">
  import Button from '@/Components/Button.vue';
  import Modal from '@/Components/Modal.vue';
  import { trans } from 'laravel-vue-i18n';
  import { toast } from 'vue3-toastify';
  import { useForm, usePage } from '@inertiajs/vue3';
  import { ref, computed, reactive } from 'vue'

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
    destroyRoute: string;
  }>();

  let selectedCheckBoxes = reactive(new Set())
  let totalSelectedCheckBoxes = computed(() => selectedCheckBoxes.size)
  let selectedcheckBox = computed(() => totalSelectedCheckBoxes.value == props.items.data.length)

  let selectAll = (checkBoxes: any) => {
    checkBoxes.forEach((checkBox: unknown) => {
      selectedCheckBoxes.add(checkBox)
    })
  }

  let clear = () => selectedCheckBoxes.clear()

  let toggle = (checkBox: any) => {
    if (selectedCheckBoxes.has(checkBox)) {
      selectedCheckBoxes.delete(checkBox)
    } else {
      selectedCheckBoxes.add(checkBox)
    }
  }

  const toggleSelection = () => {
    if (selectedcheckBox.value) {
      clear()
    } else {
      selectAll(props.items.data)
    }
  }

  const confirmingDeletionModal = ref(false);

  const openDeletionModal = () => confirmingDeletionModal.value = true

  const form = useForm({
    ids: [],
  });

  const deleteItems = () => {
    selectedCheckBoxes.forEach((checkBox: any) => form.ids.push((checkBox.id) as never))

    form.delete(route(props.routes.destroyRoute), {
      preserveScroll: true,
      onSuccess: () => closeDeletionModal(),
      onError: () => toast.error(trans(usePage().props.status as string)),
      onFinish: () => toast.success(trans(usePage().props.status as string)),
    });
  };

  const closeDeletionModal = () => confirmingDeletionModal.value = false
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

  <Button v-if="destroyRoute" color="danger" type="button" @click="openDeletionModal" start-icon="mdi:delete-outline" class="mr-2 h-full" :disabled="totalSelectedCheckBoxes === 0" />
</template>
