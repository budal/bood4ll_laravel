<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import Avatar from '@/Components/Avatar.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import TailwindIndicator from '@/Components/TailwindIndicator.vue';
import NavPopover from '@/Components/NavPopover.vue';
import { useDark, useToggle } from "@vueuse/core"
import { MoonIcon, SunIcon } from '@heroicons/vue/20/solid';

const showingNavigationDropdown = ref(false);

const isDark = useDark();
const toggleDark = useToggle(isDark);

const routeCurrent = window.location.href;

const items = [
    {
        title: "Users",
        route: "apps.users",
        description: "Manage users informations and authorizations.",
        icon: `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>`
    },
    {
        title: "Roles",
        route: "apps.roles",
        description: "Define roles, grouping abilities to define specific access.",
        icon: `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
            </svg>`
    },
    {
        title: "Units",
        route: "apps.units",
        description: "Manage staff allocation units.",
        icon: `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
            </svg>`
    },
]

</script>

<template>
    <div>
        <div class="relative min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sm:sticky sm:top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                                </Link>
                            </div>
                            <div class="hidden space-x-8 sm:-my-px sm:ml-6 sm:flex">
                                <NavLink :href="route('dashboard')" :active="routeCurrent.includes(route('dashboard'))">
                                    {{ $t('Dashboard') }}
                                </NavLink>
                                <NavPopover :items=items :active="routeCurrent.includes(route('apps'))">
                                    {{ $t('Apps') }}
                                </NavPopover>
                                <NavPopover :items=items :active="routeCurrent.includes(route('reports'))">
                                    {{ $t('Reports') }}
                                </NavPopover>
                                <NavLink :href="route('help')" :active="routeCurrent.includes(route('help'))">
                                    {{ $t('Help') }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative">
                                <button @click="toggleDark()">
                                    <SunIcon v-if="isDark" class="w-5 h-5 text-white" />
                                    <MoonIcon v-if="!isDark" class="w-5 h-5" />
                                </button>
                            </div>
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                                <Avatar :name="$page.props.auth.user.name" />
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="divide-y divide-gray-100 dark:divide-gray-600">
                                            <div class="pl-2 py-1" role="none">
                                                <div class="font-sm text-sm text-gray-800 dark:text-gray-200">{{ $page.props.auth.user.name }}</div>
                                                <div class="font-xs text-xs justify-center text-gray-500">{{ $page.props.auth.user.email }}</div>
                                            </div>
                                            <div class="py-1" role="none">
                                                <DropdownLink :href="route('profile.edit')"> {{ $t('Profile') }} </DropdownLink>
                                                <DropdownLink :href="route('settings')"> {{ $t('Settings') }} </DropdownLink>
                                            </div>
                                            <div class="py-1" role="none">
                                                <DropdownLink :href="route('messages')"> {{ $t('Messages') }} </DropdownLink>
                                                <DropdownLink :href="route('schedule')"> {{ $t('Schedule') }} </DropdownLink>
                                            </div>
                                            <div class="py-1" role="none">
                                                <DropdownLink :href="route('logout')" method="post" as="button">
                                                    {{ $t('Log Out') }}
                                                </DropdownLink>
                                            </div>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="toggleDark()" class="mr-3">
                                <SunIcon v-if="isDark" class="w-5 h-5 text-white" />
                                <MoonIcon v-if="!isDark" class="w-5 h-5" />
                            </button>
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="routeCurrent.includes(route('dashboard'))">
                            {{ $t('Dashboard') }}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('apps')" :active="routeCurrent.includes(route('apps'))">
                            {{ $t('Apps') }}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('reports')" :active="routeCurrent.includes(route('reports'))">
                            {{ $t('Reports') }}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('help')" :active="routeCurrent.includes(route('help'))">
                            {{ $t('Help') }}
                        </ResponsiveNavLink>
                    </div>

                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')" :active="route().current('profile.edit')"> {{ $t('Profile') }} </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('settings')" :active="route().current('settings')"> {{ $t('Settings') }} </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('messages')" :active="route().current('messages')"> {{ $t('Messages') }} </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('schedule')" :active="route().current('schedule')"> {{ $t('Schedule') }} </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button"> {{ $t('Log Out') }} </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white dark:bg-gray-800 shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <Breadcrumbs />
                <slot />
            </main>
        </div>
    </div>
    <TailwindIndicator />
</template>
