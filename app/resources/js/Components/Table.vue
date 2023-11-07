<script setup lang="ts">
  import { Icon } from '@iconify/vue'
  import Avatar from '@/Components/Avatar.vue';
  import Button from '@/Components/Button.vue';
  import Checkbox from '@/Components/Checkbox.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import Modal from '@/Components/Modal.vue';
  import Select from '@/Components/Select.vue';
  import Switch from '@/Components/Switch.vue';
  import SearchInput from '@/Components/SearchInput.vue';
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
    api?: string;
    softDelete?: boolean | null;
    routes?: any;
    filters?: any;
    menu?: any;
    titles: any;
    items: any;
  }>();

  const searchRoute = new URL(window.location.href);

  // toggle checkboxes
  let selectedCheckBoxes = reactive(new Set())

  let selectAll = (checkBoxes: any) => {
    checkBoxes.forEach((checkBox: unknown) => {
      selectedCheckBoxes.add(checkBox)
    })
  }

  let clear = () => {
    selectedCheckBoxes.clear()
  }

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

  const openDeletionModal = () => {
    confirmingDeletionModal.value = true;
  }

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

  const closeDeletionModal = () => {
    confirmingDeletionModal.value = false;
  };


  // restore
  const confirmRestoreModal = ref(false);
  const restoreItemID = ref('');

  const restore = (id: string) => {
    confirmRestoreModal.value = true;
    restoreItemID.value = id;
  }

  const restoreItem = () => {
    form.post(route(props.routes.restoreRoute, restoreItemID.value), {
      preserveScroll: true,
      onSuccess: () => closeRestoreModal(),
      onError: () => toast.error(trans(usePage().props.status as string)),
      onFinish: () => toast.success(trans(usePage().props.status as string)),
    });
  };

  const closeRestoreModal = () => {
    confirmRestoreModal.value = false;
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

  const trashed = ref(searchRoute.searchParams.get("trashed") || '')

  const filtersModal = ref(false);

  const openFiltersModal = () => {
    filtersModal.value = true;
  }

  const refreshFilters = () => {
    searchRoute.searchParams.set("trashed", trashed.value)

    router.visit(searchRoute, {
      method: 'get',
      preserveScroll: true,
      onSuccess: () => closeFiltersModal(),
    })
  };

  const closeFiltersModal = () => {
    filtersModal.value = false;
  };


  // toggleMenu
  const toggleState = ref(false)


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


  // switch
  const formSwitch = useForm({});

  const refArr = ref([]);

  const updateSwitch = (routeUri: string, method: 'get' | 'post', id: string) => {
    formSwitch.submit(method, route(routeUri, id), {
      preserveScroll: true,
      onError: () => toast.error(trans(usePage().props.status as string)),
      onFinish: () => toast.success(trans(usePage().props.status as string, usePage().props.statusComplements as undefined)),
    });
  }


  // td class
  const classTD = "p-2"
</script>

<template>
  <div>
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
          <Button color="secondary" @click="closeDeletionModal">
            {{ $t('Cancel') }}
          </Button>
          <Button color="danger" class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="deleteItems">
            {{ $t('Erase selected') }}
          </Button>
        </div>
      </template>
    </Modal>

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
          <Button color="secondary" @click="closeRestoreModal">
            {{ $t('Cancel') }}
          </Button>
          <Button color="success" class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="restoreItem">
            {{ $t('Restore') }}
          </Button>
        </div>
      </template>
    </Modal>

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
        <Select id="filterContent" name="filterContent" class="mt-1" :content="content" v-model="trashed" />
      </div>
      <template #buttons>
        <div class="mt-6 flex justify-end">
          <Button color="secondary" @click="closeFiltersModal">
            {{ $t('Cancel') }}
          </Button>
          <Button color="primary" class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="refreshFilters">
            {{ $t('Apply') }}
          </Button>
        </div>
      </template>
    </Modal>

    <div class="flex sticky top-0 sm:top-[65px] justify-between rounded-xl backdrop-blur-sm p-2 my-2 -mx-3 bg-secondary-light/30 dark:bg-secondary-dark/30">
      <div class="flex-none items-center">
        <Button color="danger" v-if="routes.destroyRoute" :disabled="totalSelectedCheckBoxes === 0" @click="openDeletionModal" class="mr-2 h-full">
          <Icon icon="mdi:trash-can" class="h-5 w-5" />
        </Button>
      </div>
      <div class="flex-1 items-center">
        <SearchInput :placeholder="$t('Search...')" id="search" name="search" class="w-full h-full" :value="filters.search" v-model="search" />
      </div>
      <div class="flex-none items-center">
        <Button color="secondary" @click="openFiltersModal" class="ml-2 h-full">
          <Icon icon="mdi:filter-menu" class="h-5 w-5" />
        </Button>
      </div>
      <div v-if="menu" class="flex-none items-center">
        <template v-if="menu.length > 1">
          <DropdownMenuRoot v-model:open="toggleState">
            <DropdownMenuTrigger
              class="h-full outline-none"
              :aria-label="$t('Table menu')"
            >
              <Button color="primary" class="ml-2 h-full ">
                <Icon icon="mdi:dots-horizontal" class="h-5 w-5" />
              </Button>
            </DropdownMenuTrigger>

            <DropdownMenuPortal>
              <DropdownMenuContent
                class="min-w-[220px] overflow-hidden outline-none bg-secondary-light dark:bg-secondary-dark text-primary-dark dark:text-primary-light rounded-md shadow-[0px_10px_38px_-10px_rgba(22,_23,_24,_0.35),_0px_10px_20px_-15px_rgba(22,_23,_24,_0.2)] shadow-primary-light dark:shadow-primary-dark will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
                :align="'end'"
              >
                <DropdownMenuLabel class="leading-[25px] text-center">
                  <div class="pt-2 font-xs text-sm text-primary-light dark:text-primary-dark">{{ $t('Select an option') }}</div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator class="h-[0.5px] bg-zero-dark/10 dark:bg-zero-light/5" />
                <template v-for="item in menu">
                  <Link :href="route(item.route)"> 
                    <DropdownMenuItem
                      :value="item.title"
                      :class="item.class"
                      class="px-[5px] flex pl-[10px] text-sm py-3 text-zero-light dark:text-zero-dark hover:bg-zero-light dark:hover:bg-zero-dark focus:outline-none focus:bg-zero-light dark:focus:bg-zero-dark transition duration-150 ease-in-out"
                    >
                      <Icon :icon="item.icon" class="h-5 w-5 mr-2" />

                      {{ $t(item.title) }} 
                    </DropdownMenuItem>
                  </Link>
                </template>
                <DropdownMenuArrow class="fill-secondary-light dark:fill-secondary-dark" />
              </DropdownMenuContent>
            </DropdownMenuPortal>
          </DropdownMenuRoot>
        </template>
        <template v-else>
          <Link :href="route(menu[0].route)" class="text-sm ml-2 h-full">
            <Button color="primary" class="h-full">
              <Icon :icon="menu[0].icon" class="h-5 w-5" />
            </Button>
          </Link>
        </template>
      </div>
    </div>
    <div>
      <div class="rounded-xl overflow-hidden border-2 border-secondary-light dark:border-secondary-dark">
        <div class="overflow-x-auto flex">
          <table class="table-auto w-full text-sm shadow-lg">
            <thead v-if="items.data.length > 0">
              <tr class="bg-zero-light dark:bg-zero-dark p-3 text-secondary-light dark:text-secondary-dark text-left">
                <th :class="classTD">
                  <Checkbox v-if="routes.destroyRoute" name="remember" :checked="selectedcheckBox" @click="toggleSelection" class="w-8 h-8 rounded-full" />
                </th>
                <template v-for="(content, id) in titles">
                  <th :class="classTD">
                    <Link v-if="content.disableSort != true" :href="sortBy(content.field).url" class="flex gap-1">
                      {{ $t(content.title) }}
                      <Icon icon="mdi:chevron-up" v-if="sortBy(content.field).sortMe == 'asc'" class="h-4 w-4" />
                      <Icon icon="mdi:chevron-down" v-if="sortBy(content.field).sortMe == 'desc'" class="h-4 w-4" />
                    </Link>
                  </th>
                </template>
                <th v-if="routes.editRoute" :class="classTD"></th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="item in items.data" 
                :key="`tr-${item.id}`" 
                class="bg-secondary-light dark:bg-secondary-dark hover:bg-secondary-light-hover hover:dark:bg-secondary-dark-hover border-t border-secondary-light dark:border-secondary-dark text-secondary-light dark:text-secondary-dark"
              >
                <td :class="classTD">
                  <Checkbox v-if="routes.destroyRoute && !item.deleted_at" 
                    :class="classTD" class="w-8 h-8 rounded-full" 
                    :checked="selectedCheckBoxes.has(item)" 
                    :value="item.id" 
                    :id="`checkbox-${item.id}`" 
                    @click="toggle(item)" 
                  />
                  <Button v-if="routes.restoreRoute && item.deleted_at" 
                    color="warning" padding="2" 
                    :class="classTD" 
                    @click="restore(item.id)"
                  >
                    <Icon icon="mdi:restore" class="h-5 w-5" />
                  </Button>
                </td>
                <template v-for="(content, index) in titles">
                  <td :class="classTD">
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
                </template>
                <td v-if="routes.editRoute" :class="`${classTD} text-right`">
                  <Link :href="route(routes.editRoute, item.id)">
                    <Button color="primary">
                      <Icon icon="mdi:chevron-right" class="h-5 w-5" />
                    </Button>
                  </Link>
                </td>
              </tr> 
              <tr v-if="items.data.length == 0">
                <td :class="`${classTD} text-center`">
                  <p class="text-md leading-5 text-secondary-light dark:text-secondary-dark">{{ $t('No items to show.') }}</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-if="items.total > 0" class="flex sticky bottom-0 justify-between rounded-xl backdrop-blur-sm p-2 my-2 -mx-3 bg-secondary-light/30 dark:bg-secondary-dark/30">
        <div class="w-full flex flex-row sm:hidden">
          <div class="basis-1/3 text-left">
            <Link v-if="items.prev_page_url" :href="items.prev_page_url" class="text-sm">
              <Button color="primary">{{ $t('Previous')}}</Button>
            </Link>
          </div>
          <div v-if="items.from !== null" class="basis-1/3 text-center">
            <span 
              aria-current="page" class="relative inline-flex rounded-md bg-primary-light dark:bg-primary-dark px-4 py-1 text-sm font-semibold text-primary-light dark:text-primary-dark">
              {{ `${items.current_page}/${items.last_page}` }}
            </span>
          </div>
          <div class="basis-1/3 text-right">
            <Link v-if="items.next_page_url" :href="items.next_page_url" class="text-sm">
              <Button color="primary">{{ $t('Next')}}</Button>
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
              <Link v-if="items.prev_page_url" :href="items.prev_page_url" class="text-sm">
                <Button color="primary">
                  <span class="sr-only">{{ $t('Previous')}}</span>
                  <Icon icon="mdi:chevron-left" class="h-4 w-4" />
                </Button>
              </Link>

              <template v-if="items.from !== null" v-for="item in items.links">
                <Link
                  v-if="item.label > 0 && item.label != items.current_page || item.label == items.current_page"
                  :key="item.key" :href="item.url" class="text-sm"
                >
                  <Button color="primary" :disabled="item.label == items.current_page">{{ item.label }}</Button>
                </Link>
              </template>

              <Link v-if="items.next_page_url" :href="items.next_page_url" class="text-sm">
                <Button color="primary">
                  <span class="sr-only">{{ $t('Next')}}</span>
                  <Icon icon="mdi:chevron-right" class="h-4 w-4" />
                </Button>
              </Link>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>