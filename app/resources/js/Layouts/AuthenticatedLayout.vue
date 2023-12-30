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

const menu = usePage().props.appMenu as any;

const navItems = menu.filter((item: any) => item.showInNav !== false);
const menuItems = menu;
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
                        <div class="space-x-8 sm:-my-px sm:ml-6">
                            <NavMenu :content="navItems" />
                        </div>
                    </div>
                    <div class="xs:-mr-2 flex items-center sm:ml-6">
                        <ToggleTheme />
                        <NavUser :content="menuItems">
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
