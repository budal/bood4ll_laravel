<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<script>
export default {
    props: {
        title: String,
        subtitle: String,
        description: String,
        emptyMessage: String,
        titles: Object,
        items: Object,
    }
}
</script>

<template>
    <AppLayout :title=$t(title)>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t(title) }}
            </h2>
        </template>

        <div class="py-12 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 ">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div v-if="items">
                        <div v-if="subtitle || description" class="p-6 sm:px-20 bg-white border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <div class="mt-2 text-2xl">
                                {{ $t(subtitle) }}
                            </div>

                            <div class="mt-6 text-gray-500">
                                {{ $t(description) }} 
                            </div>
                        </div>
                        <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-1">
                            <div class="w-full overflow-hidden overflow-x-auto shadow-xs">
                                <table class="w-full whitespace-no-wrap">
                                    <thead>
                                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                            <th v-for="title in titles" class="px-4 py-3">{{ $t(title) }}</th>
                                            <th class="px-4 py-3 text-center">{{ $t('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                                        <tr v-for="item in items.data" :key="item.id" class="text-gray-700 dark:text-gray-400 md:border-1 duration-500 hover:bg-gray-100 hover:dark:bg-gray-700">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center text-sm">
                                                    <div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
                                                        <img v-if="item.profile_photo_path" class="object-cover w-full h-full rounded-full" :src="'../storage/' + item.profile_photo_path" alt="" loading="lazy" />
                                                        <img v-if="!item.profile_photo_path" class="object-cover w-full h-full rounded-full" :src="'https://ui-avatars.com/api/?name='+ $t(item.name) +'&color=7F9CF5&background=EBF4FF' + item.profile_photo_path" alt="" loading="lazy" />
                                                        <div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true"></div>
                                                    </div>
                                                    <div>
                                                        <p class="font-semi bold">{{ item.name }}</p>
                                                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ item.email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ item.email }}</td>
                                            <td class="px-4 py-3 text-xs">
                                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">1</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center space-x-4 text-sm place-content-center">
                                                    <Link :href="item.id" class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-blue-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Edit">
                                                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" >
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                                        </svg>
                                                    </Link>
                                                    <Link :href="'/' + item.id" class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-red-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray" aria-label="Delete">
                                                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
                                <span class="flex items-center col-span-3">{{ $t('Showing :from-:to of :total').replace(':from', items.from).replace(':to', items.to).replace(':total', items.total) }} <!--({{ items.per_page }})--></span>
                                <span class="col-span-2"></span>
                                <span class="flex col-span-4 mt-2 sm:mt-auto sm:justify-end">
                                    <nav aria-label="Table navigation">
                                        <div class="flex flex-wrap -mb-1">
                                            <template v-for="(link, p) in items.links" :key="p">
                                                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                                                    v-html="link.label" />
                                                <Link v-else
                                                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:font-bold hover:bg-grey-500 focus:border-indigo-500 focus:text-indigo-500"
                                                    :class="{ 'bg-blue-700 text-white': link.active }" :href="link.url" v-html="link.label" />
                                            </template>
                                        </div>
                                    </nav>
                                </span>
                            </div>
                        </div>
                    </div>
                    <p v-if="!items" class="text-center p-4">{{ emptyMessage ? $t(emptyMessage) : $t("There is no items to show.") }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
