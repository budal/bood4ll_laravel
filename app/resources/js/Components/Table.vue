<script setup lang="ts">
import Button from './Button.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputLabel from '@/Components/InputLabel.vue';
import ListBox from '@/Components/ListBox.vue';
import Modal from '@/Components/Modal.vue';
import Avatar from '@/Components/Avatar.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { 
  ArrowUturnLeftIcon,
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
import { router, useForm, usePage, Link } from '@inertiajs/vue3';
import debounce from "lodash.debounce";
import { trans } from 'laravel-vue-i18n';
import { onMounted } from 'vue';

const props = defineProps<{
  api?: string;
  softDelete?: boolean | null;
  routes?: any;
  filters?: any;
  items: any;
  titles: any;
}>();

const searchRoute = new URL(window.location.href);

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
  ids: [],
});

const deleteItems = () => {
  selectedItems.forEach((item: any) => form.ids.push((item.id) as never))

  form.delete((route(props.routes.destroyRoute) as unknown ) as string, {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => toast.error(trans(usePage().props.status as string)),
    onFinish: () => toast.success(trans(usePage().props.status as string)),
  });
};

const closeModal = () => {
  confirmingUserDeletion.value = false;
};


// restore
const restoreItemModal = ref(false);
const restoreItemID = ref('');

const restore = (id: string) => {
  restoreItemModal.value = true;
  restoreItemID.value = id;
}

const restoreItem = () => {
  form.post((route(props.routes.restoreRoute, restoreItemID.value) as unknown ) as string, {
    preserveScroll: true,
    onSuccess: () => closeRestoreModal(),
    onError: () => toast.error(trans(usePage().props.status as string)),
    onFinish: () => toast.success(trans(usePage().props.status as string)),
  });
};

const closeRestoreModal = () => {
  restoreItemModal.value = false;
};


// search
const search = ref("");

const debouncedWatch = debounce(() => {
  searchRoute.searchParams.set("search", search.value)
  
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
const content = [
  { id: '', title: 'Only active' },
  { id: 'only', title: 'Only trashed', disabled: props.softDelete === true ? false : true },
  { id: 'with', title: 'Active and trashed', disabled: props.softDelete === true ? false : true },
]

const trashed = searchRoute.searchParams.get("trashed") || ''
const contentSelected = ref(content[content.map(function(e) { return e.id; }).indexOf(trashed)])

const filtersModal = ref(false);

const openFiltersModal = () => {
  filtersModal.value = true;
}

const refreshFilters = () => {
  searchRoute.searchParams.set("trashed", contentSelected.value.id)

  router.visit(searchRoute, {
    method: 'get',
    preserveScroll: true,
    onSuccess: () => closeFiltersModal(),
  })
};

const closeFiltersModal = () => {
  filtersModal.value = false;
};


// sorting column
const sortBy = (column: any) => {
  let url = new URL(window.location.href);
  let sortOrder = null;
  let sortValue = url.searchParams.get("sorted")

  if (sortValue == column) {
    url.searchParams.set("sorted", "-" + column)
    sortOrder = "asc"
  } else if (sortValue === "-" + column) {
    url.searchParams.set("sorted", column)
    sortOrder = "desc"
  } else {
    url.searchParams.set("sorted", column)
  }
  
  return {
    url: url.href,
    sortMe: sortOrder,
  }
}


// td class
const classTD = "p-2"



const data = ref({});
const loading = ref(true);
const error = ref(null);

function fetchData() {
  loading.value = true;

  return fetch(props.api, {
    method: 'get',
    headers: {
      'content-type': 'application/json'
    }
  })
  .then(res => {
    if (!res.ok) {
      const error = new Error(res.statusText);
      error.json = res.json();
      throw error;
    }

    return res.json();
  })
  .then(json => {
    data.value = json;
  })
  .catch(err => {
    error.value = err;
    if (err.json) {
      return err.json.then(json => {
        error.value.message = json.message;
      });
    }
  })
  .then(() => {
    loading.value = false;
  });
}

onMounted(() => {
  fetchData();
});

console.log(data)

</script>

<template>
  {{ data.current_page }}


  <p v-if="loading">Still loading..</p>
  <p v-if="error">{{error}}</p>
  <div>
    <Modal :show="confirmingUserDeletion" @close="closeModal">
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ $t('Are you sure you want to delete the selected items?') }}
      </h2>

      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $t('The selected items will be removed from the active items. Do you want to continue?') }}
      </p>

      <div class="mt-6 flex justify-end">
        <Button color="secondary" @click="closeModal">{{ $t('Cancel') }}</Button>

        <Button 
          color="danger"
          class="ml-3"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="deleteItems"
        >
          {{ $t('Erase selected') }}
        </Button>
      </div>
    </Modal>

    <Modal :show="restoreItemModal" @close="closeRestoreModal">
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ $t('Are you sure you want to restore this item?') }}
      </h2>

      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $t('The selected item will be restored to the active items. Do you want to continue?') }}
      </p>

      <div class="mt-6 flex justify-end">
        <Button color="secondary" @click="closeRestoreModal">{{ $t('Cancel') }}</Button>

        <Button 
          color="success"
          class="ml-3"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="restoreItem"
        >
          {{ $t('Restore') }}
        </Button>
      </div>
    </Modal>

    <Modal :show="filtersModal" @close="closeFiltersModal">
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ $t('Manage which filters to apply to the list') }}
      </h2>

      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $t('Selected filters refine searches according to your choices') }}
      </p>

      <div class="pt-3">
        <InputLabel for="name" :value="$t('Content')" />
        <ListBox :content="content" v-model="contentSelected" />
      </div>

      <div class="mt-6 flex justify-end">
        <Button color="secondary" @click="closeFiltersModal">{{ $t('Cancel') }}</Button>

        <Button color="primary"
          class="ml-3"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="refreshFilters"
        >
          {{ $t('Apply') }}
        </Button>
      </div>
    </Modal>

    <div class="flex sticky top-0 sm:top-[65px] justify-between rounded-xl backdrop-blur-sm p-2 my-2 -mx-3 bg-white/30 dark:bg-gray-800/30">
      <div class="flex-none items-center">
        <Button color="danger" v-if="props.routes.destroyRoute" :disabled="numberSelected === 0" @click="deleteSelected" class="mr-2 h-full">
          <TrashIcon class="h-5 w-5" />
        </Button>
      </div>
      <div class="flex-1 items-center">
        <SearchInput :placeholder="$t('Search...')" class="w-full h-full" :value="filters.search" v-model="search" />
      </div>
      <div class="flex-none items-center">
        <Button color="secondary" @click="openFiltersModal" class="ml-2 h-full"><AdjustmentsVerticalIcon class="h-5 w-5" /></Button>
      </div>
      <div class="flex-none items-center">
        <Button color="primary" v-if="props.routes.createRoute" class="ml-2 h-full" @click="form.get((route(props.routes.createRoute) as unknown) as string)">
          <PlusIcon class="h-5 w-5" />
        </Button>
      </div>
    </div>
    <div>
      <div class="rounded-xl overflow-hidden border-2 border-gray-200 dark:border-gray-600">
        <div class="overflow-x-auto flex">
          <table class="table-auto w-full text-sm shadow-lg">
            <thead v-if="items.total > 0 && items.from !== null">
              <tr class="bg-gray-200 dark:bg-gray-900 p-3 text-gray-1000 dark:text-white text-left">
                <th v-if="props.routes.destroyRoute" :class="`${classTD}`">
                  <Checkbox name="remember" :checked="itemsSelected" @click="toggleSelection" class="w-8 h-8 rounded-full" />
                </th>
                <template v-for="(content, id) in titles">
                  <th :class="`${classTD}`">
                    <Link v-if="content.disableSort != true" :href="sortBy(content.field).url" class="flex gap-1">
                      {{ $t(content.title) }}
                      <ChevronUpIcon v-if="sortBy(content.field).sortMe == 'asc'" class="h-4 w-4" />
                      <ChevronDownIcon v-if="sortBy(content.field).sortMe == 'desc'" class="h-4 w-4" />
                    </Link>
                  </th>
                </template>
                <th v-if="props.routes.editRoute" :class="`${classTD}`"></th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="item in items.data" 
                :key="`tr-${item.id}`" 
                class="`bg-white hover:bg-gray-100 dark:bg-gray-800 hover:dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400`"
              >
                <td :class="`${classTD}`">
                  <Checkbox v-if="props.routes.destroyRoute && !item.deleted_at" :class="`${classTD}`" class="w-8 h-8 rounded-full" :checked="selectedItems.has(item)" :value="item.id" :id="`checkbox-${item.id}`" @click="toggle(item)" />
                  <Button color="warning" padding="2" v-if="props.routes.restoreRoute && item.deleted_at" :class="`${classTD}`" @click="restore(item.id)"><ArrowUturnLeftIcon class="h-3 w-3" /></Button>
                </td>
                <template v-for="content in titles">
                  <td v-if="content.type == 'avatar'" :class="`${classTD}`">
                    <Avatar class="w-12 h-12 rounded-full" :name="`${item[content.fallback]}`" />
                  </td>
                  <td v-if="content.type == 'composite'" :class="`${classTD}`">
                    <strong class="text-gray-900 text-sm font-medium dark:text-gray-200">{{ item[content.fields[0]] }}</strong>
                    <p class="truncate text-xs leading-5 text-gray-600 dark:text-gray-400">{{ item[content.fields[1]] }}</p>
                  </td>
                  <td v-if="content.type == 'simple'" :class="`${classTD}`">
                    <p class="truncate text-xs leading-5 text-gray-900 dark:text-gray-200">{{ item[content.field] }}</p>
                  </td>
                </template>
                <td v-if="props.routes.editRoute" :class="`${classTD} text-right`">
                  <Link :href="((route(props.routes.editRoute, item.id) as unknown) as string)" as="span">
                    <Button color="primary"><ChevronRightIcon class="h-5 w-5"/></Button>
                  </Link>
                </td>
              </tr>
              <tr v-if="items.total == 0 || items.from == null">
                <td :class="`${classTD} text-center`">
                  <p class="text-lg leading-5 text-gray-600 dark:text-gray-400">{{ $t('No items to show.') }}</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-if="items.total > 0" class="flex sticky bottom-0 justify-between rounded-xl backdrop-blur-sm p-2 my-2 -mx-3 bg-white/30 dark:bg-gray-800/30">
        <div class="w-full flex flex-row sm:hidden">
          <div class="basis-1/3 text-left">
            <Link v-if="items.prev_page_url" as="button" :href="items.prev_page_url" class="text-sm">
              <Button color="primary">{{ $t('Previous')}}</Button>
            </Link>
          </div>
          <div v-if="items.from !== null" class="basis-1/3 text-center">
            <span 
              aria-current="page" class="relative inline-flex rounded-md bg-gray-600 dark:bg-gray-400 px-4 py-1 text-sm font-semibold text-white dark:text-gray-800">
              {{ `${items.current_page}/${items.last_page}` }}
            </span>
          </div>
          <div class="basis-1/3 text-right">
            <Link v-if="items.next_page_url" as="button" :href="items.next_page_url" class="text-sm">
              <Button color="primary">{{ $t('Next')}}</Button>
            </Link>
          </div>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
          <div>
            <p v-if="items.from !== null" class="hidden lg:block text-xs text-gray-800 dark:text-white">
              {{ $t('Showing :from to :to of :total results', { from: items.from, to: items.to, total: items.total }) }}
            </p>
          </div>
          <div>
            <nav class="inline-flex shadow-sm gap-[2px]" aria-label="Pagination">
              <Link v-if="items.prev_page_url" :href="items.prev_page_url" as="button" class="text-sm">
                <Button color="primary">
                  <span class="sr-only">{{ $t('Previous')}}</span>
                  <ChevronLeftIcon class="h-4 w-4" aria-hidden="true" />
                </Button>
              </Link>

              <template v-if="items.from !== null" v-for="item in items.links">
                <Link
                  v-if="item.label > 0 && item.label != items.current_page || item.label == items.current_page"
                  :key="item.key" :href="item.url" as="button" class="text-sm"
                >
                  <Button color="primary" :disabled="item.label == items.current_page">{{ item.label }}</Button>
                </Link>
              </template>

              <Link v-if="items.next_page_url" :href="items.next_page_url" as="button" class="text-sm">
                <Button color="primary">
                  <span class="sr-only">{{ $t('Next')}}</span>
                  <ChevronRightIcon class="h-4 w-4" aria-hidden="true" />
                </Button>
              </Link>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>