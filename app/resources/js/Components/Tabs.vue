<script setup lang="ts">
  import { TabsContent, TabsIndicator, TabsList, TabsRoot, TabsTrigger } from 'radix-vue'

  defineProps<{
    items: any;
    tabs?: boolean;
  }>();    

</script>

<template>
  <TabsRoot v-if="tabs === true" class="overflow-hidden rounded-xl p-2 sm:p-8 bg-zero-light dark:bg-zero-dark sm:rounded-lg shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px]" :default-value="`${items[0].id}`">
    <TabsList class="relative shrink-0 flex border-b border-zero-light dark:border-zero-dark">
      <TabsIndicator class="absolute px-8 left-0 h-[3px] bottom-0 w-[--radix-tabs-indicator-size] translate-x-[--radix-tabs-indicator-position] rounded-full transition-[width,transform] duration-300">
        <div class="bg-primary-light dark:bg-primary-dark w-full h-full" />
      </TabsIndicator>
      <TabsTrigger v-for="item in items"
        class="bg-zero-light dark:bg-zero-dark text-zero-light/50 dark:text-zero-dark/50 px-5 h-[45px] flex-1 flex items-center justify-center leading-none select-none hover:text-secondary-light hover:dark:text-secondary-dark data-[state=active]:bg-zero-light-hover data-[state=active]:dark:bg-zero-dark-hover data-[state=active]:text-zero-light data-[state=active]:dark:text-zero-dark outline-none cursor-pointer transition ease-in-out duration-500"
        :value="`${item.id}`"
      >
        {{ $t(item.title) }}
      </TabsTrigger>
    </TabsList>
    <TabsContent
      v-for="item in items"
      class="grow bg-zero-light dark:bg-zero-dark rounded-b-md outline-none"
      :value="`${item.id}`"
    >
      <div>
        <header v-if="item.title || item.subtitle" class="mb-2">
            <h2 v-if="item.title" class="text-lg font-medium text-zero-light dark:text-zero-dark">{{ $t(item.title) }}</h2>
            <p v-if="item.subtitle" class="mt-1 text-sm text-zero-light/50 dark:text-zero-dark/50">{{ $t(item.subtitle) }}</p>
        </header>
        <p class="text-zero-light dark:text-zero-dark !leading-normal">
          <slot :name="`${item.id}`" />
        </p>
      </div>

    </TabsContent>
  </TabsRoot>

  <div class="grid grid-cols-1 gap-10">
    <template v-for="item in items">
      <section 
        v-if="item.condition !== false" 
        class="p-2 rounded-xl p-2 sm:p-8 bg-zero-light dark:bg-zero-dark sm:rounded-lg shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px]"
      >
        <slot :name="`${item.id}`" />
      </section>
    </template>
  </div>
</template>