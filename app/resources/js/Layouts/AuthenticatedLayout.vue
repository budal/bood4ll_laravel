<script setup lang="ts">
import TailwindIndicator from "@/Components/TailwindIndicator.vue";
import { Link } from "@inertiajs/vue3";
import ScrollTop from "primevue/scrolltop";
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

    <ScrollTop
        :pt="{
            root: { class: 'w-2rem h-2rem bg-primary' },
            icon: { class: 'w-1rem h-1rem' },
        }"
    />
</template>
