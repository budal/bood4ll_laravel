<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/inertia-vue3';
</script>

<script>
export default {
    props: {
        title: String,
        subtitle: String,
        description: String,
        content: Object,
    },
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
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div v-if="content">
                        <div v-if="subtitle || description" class="p-6 sm:px-20 bg-gray-50 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400 dark:bg-gray-800">
                            <div v-if="subtitle" class="mt-2 text-2xl">
                                {{ $t(subtitle) }}
                            </div>

                            <div v-if="description" class="mt-6 text-gray-500">
                                {{ $t(description) }}
                            </div>
                        </div>
                        <div class="p-3 grid grid-cols-1 md:grid-cols-3 bg-opacity-25 dark:border-gray-700 dark:text-gray-400 dark:bg-gray-800">
                            <Link v-for="item in content" :href="route(item.route)" class="m-1 border rounded-lg duration-500 bg-gray-50 hover:bg-gray-300 hover:dark:bg-gray-700 hover:scale-105 dark:border-gray-700">
                                <div class="p-3 border-gray-200 md:border-1">
                                    <div class="flex items-center">
                                        <p class="w-8 h-8 text-gray-100">
                                            <div v-html="item.logo"></div>
                                        </p>
                                        <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold dark:text-gray-400">
                                            {{ $t(item.title) }}
                                        </div>
                                    </div>

                                    <div class="ml-12">
                                        <div class="mt-2 text-sm text-gray-500 text-justify">
                                            {{ $t(item.description) }}
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                    <p v-if="!content" class="text-center p-4">{{ emptyMessage ? $t(emptyMessage) : $t("There is no items to show.") }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
