<script setup lang="ts">
import SearchInput from '@/Components/SearchInput.vue';
import PrimaryButton from './PrimaryButton.vue';
import DangerButton from './DangerButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { ChevronLeftIcon, ChevronRightIcon, PlusIcon, TrashIcon } from '@heroicons/vue/20/solid'
import { ref, computed, reactive, watch, onBeforeUnmount } from 'vue'
import { router, Link } from '@inertiajs/vue3';
import debounce from "lodash.debounce";

const props = defineProps<{
    filters: any;
    items: any;
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

let selectedEmails = reactive(new Set())

let selectAll = (items: any) => {
  items.forEach((item: unknown) => {
    selectedEmails.add(item)
  })
}

let clear = () => {
  selectedEmails.clear()
}

let toggle = function(item: any) {
  if(selectedEmails.has(item)) {
    selectedEmails.delete(item)
  } else {
    selectedEmails.add(item)
  }
}

let numberSelected = computed(() => selectedEmails.size)
let itemsSelected = computed(() => numberSelected.value == props.items.data.length)

function toggleSelection() {
  if(itemsSelected.value) {
    clear()
  } else {
    selectAll(props.items.data)
  }
}

const classTD = "p-2"

</script>

<template>
  <div class="flex justify-between">
    <div class="flex items-center gap-4">
      <Link v-if="destroyRoute" :href="route(destroyRoute)"><DangerButton><TrashIcon class="h-5 w-5" /></DangerButton></Link>
    </div>
    <div class="flex items-center gap-4">
      <SearchInput :placeholder="$t('Search...')" class="z-50 mt-3 mb-3 w-96" :value="filters.search" v-model="search" />
    </div>
    <div class="flex items-center gap-4">
      <Link v-if="createRoute" :href="route(createRoute)"><PrimaryButton><PlusIcon class="h-5 w-5" /></PrimaryButton></Link>
    </div>
  </div>
  <div>
    <div class="rounded-xl overflow-hidden border-2 border-slate-200 dark:border-slate-600">
      <table class="table-auto w-full text-sm shadow-lg">
        <thead v-if="items.data.length">
          <tr class="bg-gray-200 dark:bg-slate-900 p-3 text-slate-1000 dark:text-white text-left">
            <th :class="`${classTD}`">
              <Checkbox name="remember" :checked="itemsSelected" @click="toggleSelection" class="w-8 h-8 rounded-full" />
            </th>
            <th :class="`${classTD}`">
            </th>
            <th :class="`${classTD}`">
              Song
            </th>
            <th :class="`${classTD}`">
              Year
            </th>
            <th v-if="editRoute" :class="`${classTD}`">
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items.data" :key="item.id" class="group/item bg-white hover:bg-gray-100 dark:bg-slate-800 hover:dark:bg-slate-700 border-t border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400">
            <td :class="`${classTD}`">
              <label class="flex items-center">
                <Checkbox class="w-8 h-8 rounded-full" :checked="selectedEmails.has(item)" :value="item.id" :id="item.id" @click="toggle(item)" />
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400" />
              </label>
            </td>
            <td :class="`${classTD}`">
              <img class="w-12 h-12 rounded-full" src="https://images.unsplash.com/photo-1501196354995-cbb51c65aaea?ixlib=rb-1.2.1&amp;ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&amp;auto=format&amp;fit=facearea&amp;facepad=4&amp;w=256&amp;h=256&amp;q=80">
            </td>
            <td :class="`${classTD}`">
              <strong class="text-slate-900 text-sm font-medium dark:text-slate-200">{{ item.name }}</strong>
              <p class="truncate text-xs leading-5 text-gray-600 dark:text-gray-400">{{ item.username }} / {{ item.email }}</p>
            </td>
            <td :class="`${classTD}`">
              1975
            </td>
            <td v-if="editRoute" :class="`${classTD} text-right`">
              <Link :href="route(editRoute, item.uuid)" class="group/edit md:invisible hover:bg-slate-200 group-hover/item:visible">
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