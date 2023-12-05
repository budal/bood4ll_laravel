<script setup lang="ts">
  import { isValidUrl, toast } from '@/helpers';
  import Avatar from '@/Components/Avatar.vue';
  import Button from '@/Components/Button.vue';
  import Checkbox from '@/Components/Checkbox.vue';
  import Dropdown from '@/Components/Dropdown.vue';
  import Modal from '@/Components/Modal.vue';
  import Toggle from '@/Components/Toggle.vue';
  import Search from '@/Components/Table/Search.vue';
  import Sort from '@/Components/Table/Sort.vue';
  import { router, useForm } from '@inertiajs/vue3';
  import { transChoice } from 'laravel-vue-i18n';
  import { ref, computed, reactive } from 'vue'

  const props = withDefaults(
    defineProps<{
      prefix?: string;
      id?: string;
      name?: string;
      routes?: any;
      menu?: any;
      titles: any;
      items: any;
      shortcutKey?: string;
    }>(),
    {
      routes: [],
    }
  );

  // checkboxes
  let indexRoute = new URL(window.location.href);

  let selectedItems = reactive(new Set())

  let selectedAll = computed(() => selectedItems.size == props.items.data.length)
  
  let clear = () => {
    selectedItems.clear();
    modalInfo.value = null;
  }
  
  let selectAll = (items: any) => items.forEach((item: unknown) => selectedItems.add(item))

  let toggle = (item: any) => (selectedItems.has(item)) ? selectedItems.delete(item) : selectedItems.add(item)

  const toggleAll = () => (selectedAll.value) ? clear() : selectAll(props.items.data)

  let activeItems = computed(() => {
    let items = reactive(new Set())
    selectedItems.forEach((item: any) => !item.deleted_at ? items.add(item.id) : false )
    return items
  })

  let trashedItems = computed(() => {
    let items = reactive(new Set())
    selectedItems.forEach((item: any) => item.deleted_at ? items.add(item.id) : false )
    return items
  })

  // menu
  let menuItems = computed(() => {
    let content = reactive(new Set());

    let filterFieldName = props.prefix ? `${props.prefix}_trashed` : "trashed"
    
    const showRestoreItems = indexRoute.searchParams.get(filterFieldName)
    
    if (props.routes.restoreRoute) {
      const activeRoute = new URL(window.location.href);
      activeRoute.searchParams.set(filterFieldName, 'active');

      const trashedRoute = new URL(window.location.href);
      trashedRoute.searchParams.set(filterFieldName, 'trashed');
      
      const bothRoute = new URL(window.location.href);
      bothRoute.searchParams.set(filterFieldName, 'both');

      content.add({
        title: "Filters",
        icon: "mdi:filter-outline",
        items: [
          { title: 'Only active', icon: "mdi:playlist-check", route: activeRoute.href },
          { title: 'Only trashed', icon: "mdi:playlist-remove", route: trashedRoute.href },
          { title: 'Active and trashed', icon: "mdi:list-status", route: bothRoute.href },
        ]
      })
  
      content.add({ title: "-" })
    }
  
    if (props.routes.restoreRoute && (showRestoreItems == 'trashed' || showRestoreItems == 'both')) {
      content.add({
        title: "Restore",
        icon: "mdi:restore",
        disabled: trashedItems.value.size === 0,
        list: trashedItems,
        route: props.routes.restoreRoute,
        method: "post",
        modalTitle: "Are you sure you want to restore the selected item?|Are you sure you want to restore the selected items?",
        modalSubTitle: "The selected item will be restored to the active items. Do you want to continue?|The selected items will be restored to the active items. Do you want to continue?",
        buttonTitle: "Restore selected|Restore selected",
        buttonIcon: "mdi:backup-restore",
        buttonColor: "warning",
      })
    }
  
    if (props.routes.destroyRoute && showRestoreItems != 'trashed') {
      content.add({
        title: "Remove",
        icon: "mdi:delete-outline",
        disabled: activeItems.value.size === 0,
        list: activeItems,
        route: props.routes.destroyRoute,
        method: "delete",
        modalTitle: "Are you sure you want to remove the selected item?|Are you sure you want to remove the selected items?",
        modalSubTitle: "The selected item will be removed from the active items. Do you want to continue?|The selected items will be removed from the active items. Do you want to continue?",
        buttonTitle: "Remove selected|Remove selected",
        buttonIcon: "mdi:delete-sweep-outline",
        buttonColor: "danger",
      })
    }
  
    if (props.routes.forceDestroyRoute && showRestoreItems == 'trashed') {
      content.add({
        title: "Erase",
        icon: "mdi:delete-forever-outline",
        disabled: trashedItems.value.size === 0,
        list: trashedItems,
        route: props.routes.forceDestroyRoute,
        method: "delete",
        modalTheme: "danger",
        modalTitle: "Are you sure you want to erase the selected item?|Are you sure you want to erase the selected items?",
        modalSubTitle: "The selected item will be erased from the database. This action can't be undone. Do you want to continue?|The selected items will be erased from the database. This action can't be undone. Do you want to continue?",
        buttonTitle: "Erase selected|Erase selected",
        buttonIcon: "mdi:delete-sweep-outline",
        buttonColor: "danger",
      })
    }
  
    if (props.menu) {
      content.add({ title: "-" })
      
      props.menu.forEach((item: any) => {
        if (item.list == 'checkboxes') {
          console.log(item.listCondition)

          let customList = computed(() => {
            let items = reactive(new Set())
            selectedItems.forEach((selected: any) => selected.checked === item.listCondition ? items.add(selected.id) : false )
            return items
          })

          content.add({
            title: item.title,
            icon: item.icon,
            disabled: customList.value.size === 0,
            list: customList,
            route: isValidUrl(item.route),
            method: item.method,
            modalTitle: item.modalTitle,
            modalSubTitle: item.modalSubTitle,
            buttonTitle: item.buttonTitle,
            buttonIcon: item.buttonIcon,
            buttonColor: item.buttonColor,
          })
        } else {
          content.add(item);
        }
      })
    }

    return content
  })

  const action = (item: any) => {
    if (item.list) {
      openModal(item)
    } else {
      if (item.route) {
        router.visit(
          isValidUrl(item.route),
          {
            method: item.method,
            preserveScroll: true,
          }
        );
      }
    }
  }

  // toggle
  const updateFormToogle = async (
    method: 'get' | 'post' | 'put' | 'patch' | 'delete', 
    route: any, 
    ids: any
  ) => {
    const toggleForm = useForm({ list: [] });
    
    ids.forEach((id: any) => toggleForm.list.push((id) as never))

    toggleForm.submit(method, isValidUrl(route), {
      preserveScroll: true,
     
      onSuccess: () => {
        toast()
        clear()
        toggleForm.list = []
        closeModal()
      },
      onError: () => {
        toast()
        clear()
        toggleForm.list = []
        closeModal()
      },
    });
  }

  // modal
  const confirmingDeletionModal = ref(false);

  let modalInfo = ref();

  const openModal = (item: any) => {
    modalInfo.value = item
    confirmingDeletionModal.value = true
  }

  const closeModal = () => confirmingDeletionModal.value = false

  const modalForm = useForm({ list: [] });

  const submitModal = () => {
    modalInfo.value.list.forEach((id: any) => modalForm.list.push((id) as never))

    modalForm.submit(modalInfo.value.method, isValidUrl(modalInfo.value.route), {
      preserveScroll: true,
      preserveState: false,

      onSuccess: () => {
        toast()
        clear()
        modalForm.list = []
        closeModal()
      },
      onError: () => {
        toast()
        clear()
        modalForm.list = []
        closeModal()
      },
    });
  };
</script>

<template>
  <Modal 
    v-if="modalInfo"
    :open="confirmingDeletionModal"
    :title="$t(modalInfo.modalTitle)" 
    :subTitle="$t(modalInfo.modalSubTitle)" 
    :items="modalInfo.list.size" 
    :theme="modalInfo?.modalTheme || 'secondary'"
    @close="closeModal"
  >
    <template #buttons>
        <Button color="secondary" @click="closeModal" start-icon="mdi:cancel-outline">
          {{ $t('Cancel') }}
        </Button>
        <Button :color="modalInfo.buttonColor" @click="submitModal" :start-icon="modalInfo.buttonIcon" class="ml-3" :class="{ 'opacity-25': modalForm.processing }" :disabled="modalForm.processing">
          {{ transChoice(modalInfo.buttonTitle, modalInfo.list.size) }}
        </Button>
    </template>
  </Modal>

  <div class="flex sticky top-0 sm:top-[95px] justify-between rounded-xl backdrop-blur-sm pt-1 mb-2 bg-zero-light/30 dark:bg-zero-dark/30">
    <div class="flex gap-2 w-full">
      <Dropdown v-if="menuItems" :prefix="prefix" :content="menuItems" @select="(item) => action(item)" />
      <Search :prefix="prefix" :id="id" :name="name" :shortcutKey="shortcutKey" class="flex-1" />
      <Button 
        v-if="routes.createRoute" 
        type="button"
        color="success"
        :link="routes.createRoute" 
        startIcon="mdi:plus"
        :preserveScroll="routes.createRoute.preserveScroll"
      />
    </div>
  </div>
  <div class="rounded-xl overflow-hidden border-2 border-zero-light dark:border-zero-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px]">
    <div class="overflow-x-auto flex">
      <table class="table-auto w-full text-sm shadow-lg">
        <thead v-if="items.data.length > 0">
          <tr class="bg-zero-light dark:bg-zero-dark p-3 text-zero-light dark:text-zero-dark text-left">
            <th class="p-1 sm:p-2 w-0">
              <Checkbox v-if="(routes.showCheckboxes == true || routes.destroyRoute || routes.restoreRoute)" name="remember" :checked="selectedAll" @click="toggleAll" class="w-6 h-6 sm:w-8 sm:h-8 rounded-md sm:rounded-lg" />
            </th>
            <th v-for="sort in titles" class="p-2">
              <Sort :prefix="prefix" :sort="sort" class="justify-center" />
            </th>
            <th v-if="routes.editRoute" class="p-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="item in items.data" 
            :key="`tr-${item.id}`" 
            class=" border-t text-secondary-light dark:text-secondary-dark"
            :class="item.deleted_at 
              ? 'bg-danger-light/20 dark:bg-danger-dark/20 hover:bg-danger-light-hover/20 hover:dark:bg-danger-dark-hover/20 border-danger-light/20 dark:border-danger-dark/20' 
              : 'bg-secondary-light dark:bg-secondary-dark hover:bg-secondary-light-hover hover:dark:bg-secondary-dark-hover border-secondary-light dark:border-secondary-dark'"
          >
            <td class="p-1 sm:p-2 w-0">
              <Checkbox v-if="routes.showCheckboxes == true || routes.destroyRoute || routes.restoreRoute" 
                class="w-6 h-6 sm:w-8 sm:h-8 rounded-md sm:rounded-lg" 
                :class="item.deleted_at 
                  ? ''
                  : ''"
                :checked="selectedItems.has(item)" 
                :disabled="item.inalterable === true" 
                :value="item.id" 
                :id="`checkboxItem-${item.id}`" 
                @click="toggle(item)" 
              />
            </td>
            <td v-for="content in titles" class="p-1 text-center">

              <p v-if="content.type == 'simple'" class="truncate text-xs leading-5 text-secondary-light dark:text-secondary-dark text-center">
                {{ item[content.field] ?? '-' }}
              </p>

              <template v-if="content.type == 'composite'">
                <p class="text-sm font-medium text-secondary-light dark:text-secondary-dark text-center">{{ item[content.fields[0]] ?? '-' }}</p>
                <p class="truncate text-xs text-secondary-light dark:text-secondary-dark text-center">{{ item[content.fields[1]] ?? '-' }}</p>
              </template>

              <Avatar 
                v-if="content.type == 'avatar'" 
                class="w-8 h-8 sm:w-12 sm:h-12 rounded-full" 
                :fallback="item[content.fallback]" 
              />

              <Toggle 
                v-if="content.type == 'toggle'"
                :id="item.id" 
                :name="item.name" 
                :color="content.color"
                :colorFalse="content.colorFalse"
                v-model="item.checked"
                @click="updateFormToogle(content.method, content.route, [item.id])"
              />
            </td>
            <td v-if="routes.editRoute" class="sm:p-2 w-0 text-right">
              <Button 
                type="button"
                :link="route(routes.editRoute, item.id)" 
                :srOnly="$t('Edit')" 
                startIcon="mdi:chevron-right" 
              />
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
  <div v-if="items.total > 0" class="flex sticky bottom-0 justify-between rounded-xl backdrop-blur-sm mt-5 bg-zero-light/30 dark:bg-zero-dark/30">
    <div class="w-full flex flex-row sm:hidden">
      <div class="basis-1/3 text-left">
        <Button 
          v-if="items.prev_page_url" 
          type="button"
          :link="items.prev_page_url" 
          :title="$t('Previous')" 
          :preserveScroll="true"
        />
      </div>
      <div v-if="items.from !== null" class="basis-1/3 text-center">
        <span 
          aria-current="page" 
          class="relative inline-flex rounded-md bg-zero-light dark:bg-zero-dark px-4 py-1 text-sm font-semibold text-zero-light dark:text-zero-dark"
        >
          {{ `${items.current_page}/${items.last_page}` }}
        </span>
      </div>
      <div class="basis-1/3 text-right">
        <Button 
          v-if="items.next_page_url" 
          :link="items.next_page_url" 
          :title="$t('Next')" 
          :preserveScroll="true"
        />
      </div>
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p v-if="items.from !== null" class="hidden lg:block text-xs text-secondary-light dark:text-secondary-dark">
          {{ $t('Showing :from to :to of :total results', { from: items.from, to: items.to, total: items.total }) }}
        </p>
      </div>
      <div>
        <nav class="inline-flex gap-[2px]" aria-label="Pagination">
          <Button 
            v-if="items.current_page > items.last_page ? items.last_page_url : items.prev_page_url" 
            type="button"
            :link="items.current_page > items.last_page ? items.last_page_url : items.prev_page_url" 
            :srOnly="$t('Previous')" 
            startIcon="mdi:chevron-left" 
            :preserveScroll="true"
          />
          <template v-if="items.from !== null" v-for="item in items.links">
            <Button 
              v-if="item.label > 0 && item.label != items.current_page || item.label == items.current_page"
              type="button"
              :link="item.url" 
              :title="item.label" 
              :preserveScroll="true"
              :disabled="item.label == items.current_page"
            />
          </template>
          <Button 
            v-if="items.next_page_url" 
            type="button"
            :link="items.next_page_url" 
            :srOnly="$t('Next')" 
            startIcon="mdi:chevron-right" 
            :preserveScroll="true"
          />
        </nav>
      </div>
    </div>
  </div>
</template>