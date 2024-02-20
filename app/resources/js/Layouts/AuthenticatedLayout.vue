<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Avatar from "@/Components/Avatar.vue";
import Bullet from "@/Components/Bullet.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import NavMenu from "@/Components/NavMenu.vue";
import NavUser from "@/Components/NavUser.vue";
import ScrollToTop from "@/Components/ScrollToTop.vue";
import TailwindIndicator from "@/Components/TailwindIndicator.vue";
import ToggleTheme from "@/Components/ToggleTheme.vue";
import { Link, usePage } from "@inertiajs/vue3";
// @ts-expect-error
import { Modal } from "/vendor/emargareten/inertia-modal";
import NavBar from "@/Components/NavBar.vue";
</script>

<template>
    <div class="relative min-h-screen bg-zero-white dark:bg-zero-black">
        <nav class="bg-zero-light dark:bg-zero-dark sm:sticky sm:top-0 z-[10]">
            <div
                v-if="$page.props.auth.previousUser === true"
                class="font-medium bg-danger-light dark:bg-danger-dark text-xs text-center text-danger-light dark:text-danger-dark"
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
                <NavBar />
            </div>
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
