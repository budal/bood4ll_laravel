<script setup lang="ts">
  import { Icon } from '@iconify/vue'
  import Avatar from '@/Components/Avatar.vue';
  import Button from '@/Components/Button.vue';
  import Checkbox from '@/Components/Checkbox.vue';
  import Modal from '@/Components/Modal.vue';
  import Switch from '@/Components/Switch.vue';
  import Deletion from '@/Components/Table/Deletion.vue';
  import Filter from '@/Components/Table/Filter.vue';
  import Menu from '@/Components/Table/Menu.vue';
  import Restore from '@/Components/Table/Restore.vue';
  import Search from '@/Components/Table/Search.vue';
  import Sort from '@/Components/Table/Sort.vue';
  import { trans } from 'laravel-vue-i18n';
  import { toast } from 'vue3-toastify';
  import { useForm, usePage, Link } from '@inertiajs/vue3';
  import { ref, computed, reactive } from 'vue'

  const props = defineProps<{
    id?: string;
    name?: string;
    softDelete?: boolean | null;
    routes?: any;
    filters?: any;
    menu?: any;
    titles: any;
    items: any;
  }>();

  // toggle checkboxes
  let selectedCheckBoxes = reactive(new Set())

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

  let totalSelectedCheckBoxes = computed(() => selectedCheckBoxes.size)
  let selectedcheckBox = computed(() => totalSelectedCheckBoxes.value == props.items.data.length)

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


  // switch
  const formSwitch = useForm({});

  const updateSwitch = (routeUri: string, method: 'get' | 'post', id: string) => {
    formSwitch.submit(method, route(routeUri, id), {
      preserveScroll: true,
      onError: () => toast.error(trans(usePage().props.status as string)),
      onFinish: () => toast.success(trans(usePage().props.status as string, usePage().props.statusComplements as undefined)),
    });
  }

  let searchName = (Math.random() + 1).toString(36).substring(7);
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

  <div class="flex sticky top-0 sm:top-[95px] justify-between rounded-xl backdrop-blur-sm pt-1 mb-2 bg-secondary-light/30 dark:bg-secondary-dark/30">
    <div class="flex-none items-center">
      <!-- <Deletion :destroyRoute="routes.destroyRoute" /> -->
      <Button v-if="routes.destroyRoute" color="danger" type="button" @click="openDeletionModal" start-icon="mdi:delete-outline" class="mr-2 h-full" :disabled="totalSelectedCheckBoxes === 0" />
    </div>
    <div class="flex-1 items-center">
      <Search :id="id" :name="name" :search="filters.search" />
    </div>
    <div class="flex-none items-center">
      <Filter :softDelete="softDelete"/>
    </div>
    <div v-if="menu" class="flex-none items-center">
      <Menu :menu="menu" />
    </div>
  </div>
  <div class="rounded-xl overflow-hidden border-2 border-secondary-light dark:border-secondary-dark">
    <div class="overflow-x-auto flex">
      <table class="table-auto w-full text-sm shadow-lg">
        <thead v-if="items.data.length > 0">
          <tr class="bg-zero-light dark:bg-zero-dark p-3 text-secondary-light dark:text-secondary-dark text-left">
            <th class="p-2">
              <Checkbox v-if="routes.destroyRoute" name="remember" :checked="selectedcheckBox" @click="toggleSelection" class="w-8 h-8 rounded-full" />
            </th>
            <th v-for="sort in titles" class="p-2">
              <Sort :sort="sort"/>
            </th>
            <th v-if="routes.editRoute" class="p-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="item in items.data" 
            :key="`tr-${item.id}`" 
            class="bg-secondary-light dark:bg-secondary-dark hover:bg-secondary-light-hover hover:dark:bg-secondary-dark-hover border-t border-secondary-light dark:border-secondary-dark text-secondary-light dark:text-secondary-dark"
          >
            <td class="p-2">
              <Checkbox v-if="routes.destroyRoute && !item.deleted_at" 
                class="w-8 h-8 rounded-full" 
                :checked="selectedCheckBoxes.has(item)" 
                :value="item.id" 
                :id="`checkbox-${item.id}`" 
                @click="toggle(item)" 
              />
              <Restore :restoreRoute="routes.restoreRoute" :item="item" />
            </td>
            <td v-for="content in titles" class="p-1">
              <p v-if="content.type == 'simple'" class="truncate text-xs leading-5 text-secondary-light dark:text-secondary-dark">
                {{ item[content.field] ?? '-' }}
              </p>
              <template v-if="content.type == 'composite'">
                <strong class="text-sm font-medium text-secondary-light dark:text-secondary-dark">{{ item[content.fields[0]] ?? '-' }}</strong>
                <p class="truncate text-xs leading-5 text-secondary-light dark:text-secondary-dark">{{ item[content.fields[1]] ?? '-' }}</p>
              </template>
              <Avatar 
                v-if="content.type == 'avatar'" 
                class="w-12 h-12 rounded-full" 
                :fallback="item[content.fallback]" 
              />
              <Switch 
                v-if="content.type == 'switch'" 
                :name="item.name" 
                :value="item.id" 
                :checked="item.checked" 
                @click="updateSwitch(content.route, content.method, item.id)"
              />
            </td>
            <td v-if="routes.editRoute" class="p-2 text-right">
              <Link as="span" :href="route(routes.editRoute, item.id)">
                <Button color="primary" type="button">
                  <Icon icon="mdi:chevron-right" class="h-5 w-5" />
                </Button>
              </Link>
            </td>
          </tr> 
          <tr v-if="items.data.length == 0">
            <td class="p-4 text-center">
              <p class="text-md leading-5 text-secondary-light dark:text-secondary-dark">{{ $t('No items to show.') }}</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div v-if="items.total > 0" class="flex sticky bottom-0 justify-between rounded-xl backdrop-blur-sm mt-5 bg-secondary-light/30 dark:bg-secondary-dark/30">
    <div class="w-full flex flex-row sm:hidden">
      <div class="basis-1/3 text-left">
        <Link as="span" v-if="items.prev_page_url" :href="items.prev_page_url" class="text-sm">
          <Button color="primary" type="button">{{ $t('Previous')}}</Button>
        </Link>
      </div>
      <div v-if="items.from !== null" class="basis-1/3 text-center">
        <span 
          aria-current="page" class="relative inline-flex rounded-md bg-primary-light dark:bg-primary-dark px-4 py-1 text-sm font-semibold text-primary-light dark:text-primary-dark">
          {{ `${items.current_page}/${items.last_page}` }}
        </span>
      </div>
      <div class="basis-1/3 text-right">
        <Link as="span" v-if="items.next_page_url" :href="items.next_page_url" class="text-sm">
          <Button color="primary" type="button">{{ $t('Next')}}</Button>
        </Link>
      </div>
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p v-if="items.from !== null" class="hidden lg:block text-xs text-secondary-light dark:text-secondary-dark">
          {{ $t('Showing :from to :to of :total results', { from: items.from, to: items.to, total: items.total }) }}
        </p>
      </div>
      <div>
        <nav class="inline-flex shadow-sm gap-[2px]" aria-label="Pagination">
          <Link as="span" v-if="items.prev_page_url" :href="items.prev_page_url" class="text-sm">
            <Button color="primary" type="button">
              <span class="sr-only">{{ $t('Previous')}}</span>
              <Icon icon="mdi:chevron-left" class="h-4 w-4" />
            </Button>
          </Link>
          <template v-if="items.from !== null" v-for="item in items.links">
            <Link
              as="span"
              v-if="item.label > 0 && item.label != items.current_page || item.label == items.current_page"
              :key="item.key" :href="item.url" class="text-sm"
            >
              <Button color="primary" type="button" :disabled="item.label == items.current_page">{{ item.label }}</Button>
            </Link>
          </template>
          <Link as="span" v-if="items.next_page_url" :href="items.next_page_url" class="text-sm">
            <Button color="primary" type="button">
              <span class="sr-only">{{ $t('Next')}}</span>
              <Icon icon="mdi:chevron-right" class="h-4 w-4" />
            </Button>
          </Link>
        </nav>
      </div>
    </div>
  </div>
</template>