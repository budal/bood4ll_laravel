<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import NavUser from "@/Components/NavUser.vue";
import Avatar from "@/Components/Avatar.vue";
import Bullet from "@/Components/Bullet.vue";
import ScrollToTop from "@/Components/ScrollToTop.vue";
import TailwindIndicator from "@/Components/TailwindIndicator.vue";
import ToggleTheme from "@/Components/ToggleTheme.vue";
import NavMenu from "@/Components/NavMenu.vue";
// @ts-expect-error
import { Modal } from "/vendor/emargareten/inertia-modal";
import { Link, usePage } from "@inertiajs/vue3";

const apps = [
    {
        title: "Users",
        route: "apps.users.index",
        description: "Manage users informations and authorizations.",
        icon: `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>`,
    },
    {
        title: "Roles",
        route: "apps.roles.index",
        description:
            "Define roles, grouping abilities to define specific access.",
        icon: `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                </svg>`,
    },
    {
        title: "Units",
        route: "apps.units.index",
        description: "Manage the units registered in the system.",
        icon: `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                </svg>`,
    },
];

const menuNav = [
    {
        title: "Dashboard",
        icon: "mdi:monitor-dashboard",
        route: "dashboard",
        class: "sm:hidden",
    },
    {
        title: "Apps",
        icon: "mdi:apps-box",
        route: "apps",
        class: "sm:hidden",
        links: apps,
    },
    {
        title: "Reports",
        icon: "mdi:chart-areaspline",
        route: "reports",
        class: "sm:hidden",
        links: apps,
    },
    {
        title: "Help",
        icon: "mdi:help-circle-outline",
        route: "help",
        class: "sm:hidden",
    },
];

const menuUser = [
    {
        title: "Dashboard",
        route: "dashboard",
        class: "sm:hidden",
    },
    {
        title: "Apps",
        route: "apps",
        class: "sm:hidden",
        links: apps,
    },
    {
        title: "Reports",
        route: "reports",
        class: "sm:hidden",
        links: apps,
    },
    {
        title: "Help",
        route: "help",
        class: "sm:hidden",
    },
    {
        title: "-",
        class: "sm:hidden",
    },
    {
        title: "Profile",
        route: "profile.edit",
    },
    {
        title: "Settings",
        route: "settings",
    },
    {
        title: "Messages",
        route: "messages",
    },
    {
        title: "Schedule",
        route: "schedule",
    },
];
</script>

<template>
    <div class="relative min-h-screen bg-zero-white dark:bg-zero-black">
        <nav class="bg-zero-light dark:bg-zero-dark sm:sticky sm:top-0 z-[10]">
            <div
                v-if="$page.props.auth.previousUser === true"
                :class="`font-medium bg-danger-light dark:bg-danger-dark text-xs text-center text-danger-light dark:text-danger-dark`"
            >
                {{
                    $t(
                        "You are managing information as a different account than you are logged in to. Be cautious.",
                    )
                }}
                <Link
                    :href="route('apps.users.return_to_my_user')"
                    as="button"
                    method="post"
                    >[ {{ $t("Log out") }} ]
                </Link>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link
                                :href="route('home')"
                                class="p-1 rounded-full focus:outline-none focus:ring-2 focus:ring-zero-light dark:focus:ring-zero-dark focus:ring-offset-1 focus:ring-offset-zero-light dark:focus:ring-offset-zero-dark transition ease-in-out duration-500"
                            >
                                <ApplicationLogo
                                    class="block h-9 w-auto fill-current text-zero-light dark:text-zero-dark"
                                />
                            </Link>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ml-6 sm:flex">
                            <NavMenu :content="menuNav" />
                        </div>
                    </div>
                    <div class="xs:-mr-2 flex items-center sm:ml-6">
                        <ToggleTheme />
                        <NavUser :content="menuUser">
                            <template #trigger>
                                <Bullet>
                                    <Avatar
                                        :fallback="$page.props.auth.user.name"
                                    />
                                </Bullet>
                            </template>
                        </NavUser>
                    </div>
                </div>
            </div>
            <Breadcrumbs />
        </nav>
        <!-- <div class="sm:sticky sm:top-[65px] z-[5]"> -->
        <!-- </div> -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <slot />
            </div>
        </div>
    </div>
    <Modal />
    <TailwindIndicator />
    <ScrollToTop />
</template>
