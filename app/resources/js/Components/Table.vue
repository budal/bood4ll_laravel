<script setup lang="ts">
import PrimaryButton from './PrimaryButton.vue';
import DangerButton from './DangerButton.vue';
import Avatar from '@/Components/Avatar.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { ChevronLeftIcon, ChevronRightIcon, PlusIcon, TrashIcon } from '@heroicons/vue/20/solid'
import { ref, computed, reactive, watch, onBeforeUnmount } from 'vue'
import { toast } from 'vue3-toastify';
import { router, useForm, Link } from '@inertiajs/vue3';
import debounce from "lodash.debounce";

const props = defineProps<{
    status?: any;
    filters: any;
    items: any;
    titles: any;
    indexRoute: string;
    createRoute?: string;
    editRoute?: string;
    destroyRoute?: string;
      restoreRoute?: string;
}>();

const search = ref("");

const routeCurrent = route(props.indexRoute);

const debouncedWatch = debounce(() => {
  router.visit(routeCurrent+'?search='+search.value, {
    method: 'get',
    preserveState: true,
  })
}, 500);

watch(search, debouncedWatch);

onBeforeUnmount(() => {
  debouncedWatch.cancel();
})

let selectedItems = reactive(new Set())

let selectAll = (items: any) => {
  items.forEach((item: unknown) => {
    selectedItems.add(item)
  })
}

let clear = () => {
  selectedItems.clear()
}

let toggle = (item: any) => {
  if (selectedItems.has(item)) {
    selectedItems.delete(item)
  } else {
    selectedItems.add(item)
  }
}

let numberSelected = computed(() => selectedItems.size)
let itemsSelected = computed(() => numberSelected.value == props.items.data.length)

const toggleSelection = () => {
  if (itemsSelected.value) {
    clear()
  } else {
    selectAll(props.items.data)
  }
}

const confirmingUserDeletion = ref(false);

const deleteSelected = () => {
  confirmingUserDeletion.value = true;
}

const form = useForm({
  uuids: [],
});

let selectedItemsUUIDs = reactive(new Set())

const deleteUser = () => {
  const uuids = selectedItems.forEach((item: any) => {
    selectedItemsUUIDs.add(item.uuid)
  })

  form.uuids = selectedItemsUUIDs
  
  console.log(selectedItemsUUIDs)

  form.delete(route(props.destroyRoute), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => toast.error('Wow so easy !'),//session('status')
      onFinish: () => toast.success(props.status),//,
  });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
const classTD = "p-2"

</script>

<template>
  <Modal :show="confirmingUserDeletion" @close="closeModal">
    <div class="p-6">
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ $t('Are you sure you want to delete the following items?') }}
      </h2>

      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $t('The following items will be removed from the active items. Do you want to continue?') }}
      </p>

      <div class="mt-6 flex justify-end">
        <SecondaryButton @click="closeModal">{{ $t('Cancel') }}</SecondaryButton>

        <DangerButton
          class="ml-3"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="deleteUser"
        >
            {{ $t('Delete items') }}
        </DangerButton>
      </div>
    </div>
  </Modal>

  <div class="flex justify-between my-4">
    <div class="flex-none items-center">
      <DangerButton v-if="destroyRoute" :disabled="numberSelected === 0" @click="deleteSelected"><TrashIcon class="h-6 w-6" /></DangerButton>
    </div>
    <div class="flex-1 items-center px-4">
      <SearchInput :placeholder="$t('Search...')" class="w-full" :value="filters.search" v-model="search" />
    </div>
    <div class="flex-none items-center">
      <Link v-if="createRoute" as="button" :href="route(createRoute)"><PrimaryButton><PlusIcon class="h-6 w-6" /></PrimaryButton></Link>
    </div>
  </div>
  <div>
    <div class="rounded-xl overflow-hidden border-2 border-slate-200 dark:border-slate-600">
      <table class="table-auto w-full text-sm shadow-lg">
        <thead v-if="items.data.length">
          <tr class="bg-gray-200 dark:bg-slate-900 p-3 text-slate-1000 dark:text-white text-left">
            <th v-if="destroyRoute" :class="`${classTD}`">
              <Checkbox name="remember" :checked="itemsSelected" @click="toggleSelection" class="w-8 h-8 rounded-full" />
            </th>
            <template v-for="(content, id) in titles">
              <th :class="`${classTD}`">
                {{ $t(content.title) }}
              </th>
            </template>
            <th v-if="editRoute" :class="`${classTD}`"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items.data" :key="`tr-${item.uuid}`" class="group/item bg-white hover:bg-gray-100 dark:bg-slate-800 hover:dark:bg-slate-700 border-t border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400">
            <td v-if="destroyRoute" :class="`${classTD}`">
              <Checkbox class="w-8 h-8 rounded-full" :checked="selectedItems.has(item)" :value="item.uuid" :id="`checkbox-${item.uuid}`" @click="toggle(item)" />
            </td>
            <template v-for="content in titles">
              <td v-if="content.type == 'avatar'" :class="`${classTD}`">
                <Avatar class="w-12 h-12 rounded-full" :name="`${item[content.fallback]}`" />
              </td>
              <td v-if="content.type == 'composite'" :class="`${classTD}`">
                <strong class="text-slate-900 text-sm font-medium dark:text-slate-200">{{ item[content.fields[0]] }}</strong>
                <p class="truncate text-xs leading-5 text-gray-600 dark:text-gray-400">{{ item[content.fields[1]] }}</p>
              </td>
              <td v-if="content.type == 'simple'" :class="`${classTD}`">
                {{ item[content.field] }}
              </td>
            </template>
            <td v-if="editRoute" :class="`${classTD} text-right`">
              <Link :href="route(editRoute, item.uuid)" as="button" class="rounded-lg group/edit md:invisible hover:bg-slate-200 group-hover/item:visible">
                <PrimaryButton class="p-1">
                  <ChevronRightIcon
                    class="h-5 w-5 group-hover/edit:translate-x-0.5 group-hover/edit:text-slate-500"
                    aria-hidden="true"
                  />
                </PrimaryButton>
              </Link>
            </td>
          </tr>
          <tr v-if="!items.data.length">
            <td :class="`${classTD} text-right`">
              {{ $t('No items to show.') }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="items.last_page > 1" class="flex items-center justify-between bg-white dark:bg-gray-800 px-4 py-3 sm:px-6">
      <div class="w-full flex justify-between sm:hidden">
        <div>
          <Link v-if="items.prev_page_url" :href="items.prev_page_url" class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-700 bg-gray-800 dark:bg-gray-200 px-4 py-2 text-sm uppercase font-medium text-white dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">{{ $t('Previous')}}</Link>
        </div>
        <div>
          <Link v-if="items.next_page_url" :href="items.next_page_url" class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-700 bg-gray-800 dark:bg-gray-200 px-4 py-2 text-sm uppercase font-medium text-white dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">{{ $t('Next')}}</Link>
        </div>
      </div>
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <p class="hidden lg:block text-xs text-gray-800 dark:text-white">
            {{ $t('Showing :from to :to of :total results', { from: items.from, to: items.to, total: items.total }) }}
          </p>
        </div>
        <div>
          <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
            <span 
                v-if="!items.prev_page_url"
                aria-current="page" class="relative inline-flex items-center rounded-l-md px-1 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
            </span>
            <Link v-if="items.prev_page_url" :href="items.prev_page_url" class="relative inline-flex items-center rounded-l-md px-2 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
              <span class="sr-only">{{ $t('Previous')}}</span>
              <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
            </Link>
            <span v-for="item in items.links">
              <Link
                v-if="item.label > 0 && item.label != items.current_page"
                :key="item.key" :href="item.url" :active="item.active" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
                {{ item.label }}
              </Link>
              <span 
                v-if="item.label == items.current_page"
                aria-current="page" class="relative z-10 inline-flex items-center bg-gray-400 px-4 py-2 text-sm font-semibold text-white dark:text-gray-800 focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">
                {{ item.label }}
              </span>
            </span>
            <Link v-if="items.next_page_url" :href="items.next_page_url" class="relative inline-flex items-center rounded-r-md px-2 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
              <span class="sr-only">{{ $t('Next')}}</span>
              <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
            </Link>
            <span 
                v-if="!items.next_page_url"
                aria-current="page" class="relative inline-flex items-center rounded-r-md px-1 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 ring-1 ring-inset ring-gray-600 dark:ring-gray-300 hover:bg-gray-700 dark:hover:bg-white focus:z-20 focus:outline-offset-0">
            </span>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>