<script setup lang="ts">
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from './DangerButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Select from '@/Components/Select.vue';
import Modal from '@/Components/Modal.vue';
import Avatar from '@/Components/Avatar.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { 
  ChevronUpIcon, 
  ChevronDownIcon, 
  ChevronLeftIcon, 
  ChevronRightIcon, 
  PlusIcon, 
  TrashIcon, 
  AdjustmentsVerticalIcon 
} from '@heroicons/vue/20/solid'
import { ref, computed, reactive, watch, onBeforeUnmount } from 'vue'
import { toast } from 'vue3-toastify';
import { router, useForm, Link } from '@inertiajs/vue3';
import debounce from "lodash.debounce";

const props = defineProps<{
    status?: any;
    filters?: any;
    items: any;
    titles: any;
    indexRoute: string;
    createRoute?: string;
    editRoute?: string;
    destroyRoute?: string;
      restoreRoute?: string;
}>();

const routeCurrent = window.location.href;

// deletion checkboxes
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

const deleteUser = () => {
  selectedItems.forEach((item: any) => {
    form.uuids.push(item.uuid)
  })

  form.delete(route(props.destroyRoute), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => toast.error(props.status),
      onFinish: () => toast.success(props.status),
  });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
};

// search
const search = ref("");

const debouncedWatch = debounce(() => {
  const searchRoute = new URL(routeCurrent);
  
  const sort = searchRoute.searchParams.get("sort") || ''
  
  searchRoute.searchParams.set("search", search.value)
  searchRoute.searchParams.set("sort", sort)

  router.visit(searchRoute, {
    method: 'get',
    preserveState: true,
  })
}, 500);

watch(search, debouncedWatch);

onBeforeUnmount(() => {
  debouncedWatch.cancel();
})

// filters modal
const filtersModal = ref(false);

const openFiltersModal = () => {
  filtersModal.value = true;
}

const refreshFilters = () => {
};

const closeFiltersModal = () => {
  filtersModal.value = false;
};

// sort column
const sort = (column: any) => {
  let url = new URL(routeCurrent)
  let sort = null;
  let sortValue = url.searchParams.get("sort")
  let searchMe = search.value || url.searchParams.get("search")

  url.searchParams.set("search", searchMe)

  if (sortValue == column) {
    url.searchParams.set("sort", "-" + column)
    sort = "asc"
  } else if (sortValue === "-" + column) {
    url.searchParams.set("sort", column)
    sort = "desc"
  } else {
    url.searchParams.set("sort", column)
  }

  return {
    url: url.href,
    sort: sort,
  }
}

// td class
const classTD = "p-2"

</script>

<template>
  <div>
    <Modal :show="confirmingUserDeletion" @close="closeModal">
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ $t('Are you sure you want to delete the selected items?') }}
      </h2>

      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $t('The selected items will be removed from the active items. Do you want to continue?') }}
      </p>

      <div class="mt-6 flex justify-end">
        <SecondaryButton @click="closeModal">{{ $t('Cancel') }}</SecondaryButton>

        <DangerButton
          class="ml-3"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="deleteUser"
        >
            {{ $t('Erase selected') }}
        </DangerButton>
      </div>
    </Modal>

    <Modal :show="filtersModal" @close="closeFiltersModal">
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ $t('Manage which filters to apply to the list') }}
      </h2>

      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $t('The selected items will be removed from the active items. Do you want to continue?') }}
      </p>

      <Select />

      <div class="mt-6 flex justify-end">
        <SecondaryButton @click="closeFiltersModal">{{ $t('Cancel') }}</SecondaryButton>

        <PrimaryButton
          class="ml-3"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="deleteUser"
        >
            {{ $t('Apply') }}
        </PrimaryButton>
      </div>
    </Modal>


    <div class="flex sticky top-0 sm:top-[65px] justify-between rounded-xl backdrop-blur-sm p-2 my-2 -mx-3 bg-white/30 dark:bg-gray-800/30">
      <div class="flex-none items-center">
        <DangerButton v-if="destroyRoute" :disabled="numberSelected === 0" @click="deleteSelected" class="mr-2 h-full">
          <TrashIcon class="h-5 w-5" />
        </DangerButton>
      </div>
      <div class="flex-1 items-center">
        <SearchInput :placeholder="$t('Search...')" class="w-full h-full" :value="filters.search" v-model="search" />
      </div>
      <div class="flex-none items-center">
        <SecondaryButton @click="openFiltersModal" class="ml-2 h-full"><AdjustmentsVerticalIcon class="h-5 w-5" /></SecondaryButton>
      </div>
      <div class="flex-none items-center">
        <PrimaryButton v-if="createRoute" class="ml-2 h-full" @click="form.get(route(createRoute))">
          <PlusIcon class="h-5 w-5" />
        </PrimaryButton>
      </div>
    </div>
    <div>
      <div class="rounded-xl overflow-hidden border-2 border-slate-200 dark:border-slate-600">
        <div class="overflow-x-auto flex">
          <table class="table-auto w-full text-sm shadow-lg">
            <thead v-if="items.data.length">
              <tr class="bg-gray-200 dark:bg-slate-900 p-3 text-slate-1000 dark:text-white text-left">
                <th v-if="destroyRoute" :class="`${classTD}`">
                  <Checkbox name="remember" :checked="itemsSelected" @click="toggleSelection" class="w-8 h-8 rounded-full" />
                </th>
                <template v-for="(content, id) in titles">
                  <th :class="`${classTD}`">
                    <Link :href="sort(content.field).url" class="flex gap-1">
                      {{ $t(content.title) }}
                      <ChevronUpIcon v-if="sort(content.field).sort == 'asc'" class="h-4 w-4" />
                      <ChevronDownIcon v-if="sort(content.field).sort == 'desc'" class="h-4 w-4" />
                    </Link>
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
                  <Link :href="route(editRoute, item.uuid)" as="button">
                    <PrimaryButton><ChevronRightIcon class="h-5 w-5"/></PrimaryButton>
                  </Link>
                </td>
              </tr>
              <tr v-if="!items.data.length">
                <td :class="`${classTD} text-center`">
                  <p class="text-lg leading-5 text-gray-600 dark:text-gray-400">{{ $t('No items to show.') }}</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-if="items.last_page > 1" class="flex sticky bottom-0 justify-between rounded-xl backdrop-blur-sm p-2 my-2 -mx-3 bg-white/30 dark:bg-gray-800/30">
        <div class="w-full flex flex-row sm:hidden">
          <div class="basis-1/3 text-left">
            <Link v-if="items.prev_page_url" as="button" :href="items.prev_page_url" class="text-sm">
              <PrimaryButton>{{ $t('Previous')}}</PrimaryButton>
            </Link>
          </div>
          <div class="basis-1/3 text-center">
            <span 
              aria-current="page" class="relative inline-flex rounded-md bg-gray-600 dark:bg-gray-400 px-4 py-1 text-sm font-semibold text-white dark:text-gray-800">
              {{ `${items.current_page}/${items.last_page}` }}
            </span>
          </div>
          <div class="basis-1/3 text-right">
            <Link v-if="items.next_page_url" as="button" :href="items.next_page_url" class="text-sm">
              <PrimaryButton>{{ $t('Next')}}</PrimaryButton>
            </Link>
          </div>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
          <div>
            <p class="hidden lg:block text-xs text-gray-800 dark:text-white">
              {{ $t('Showing :from to :to of :total results', { from: items.from, to: items.to, total: items.total }) }}
            </p>
          </div>
          <div>
            <nav class="inline-flex shadow-sm gap-[2px]" aria-label="Pagination">
              <Link v-if="items.prev_page_url" :href="items.prev_page_url" as="button" class="text-sm">
                <PrimaryButton>
                  <span class="sr-only">{{ $t('Previous')}}</span>
                  <ChevronLeftIcon class="h-4 w-4" aria-hidden="true" />
                </PrimaryButton>
              </Link>

              <template v-for="item in items.links">
                <Link
                  v-if="item.label > 0 && item.label != items.current_page || item.label == items.current_page"
                  :key="item.key" :href="item.url" as="button" class="text-sm"
                >
                  <PrimaryButton :disabled="item.label == items.current_page">{{ item.label }}</PrimaryButton>
                </Link>
              </template>

              <Link v-if="items.next_page_url" :href="items.next_page_url" as="button" class="text-sm">
                <PrimaryButton>
                  <span class="sr-only">{{ $t('Next')}}</span>
                  <ChevronRightIcon class="h-4 w-4" aria-hidden="true" />
                </PrimaryButton>
              </Link>
            </nav>
          </div>
        </div>
      </div>
    </div>
   </div>
</template>