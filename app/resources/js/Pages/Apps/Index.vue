<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Apps from './Partials/Apps.vue';

import { Head } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const items = [
    {
        title: "Users",
        route: "apps.users",
        description: "Manage users informations and authorizations.",
    },
    {
        title: "Roles",
        route: "apps.users",
        description: "Set roles for access to specific resources.",
    },
    {
        title: "Units",
        route: "apps.users",
        description: "Manage staff allocation units.",
    },

]
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $t('Apps') }}</h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ $t('Select the application you want to use.') }}
                            </p>
                        </header>

                        <div class="mt-6 grid grid-cols-1 items-stretch sm:grid-cols-2 md:grid-cols-3 gap-5">
                            <Apps v-for="item in items" :title="item.title" :description="item.description" :href="route(item.route)" />
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
