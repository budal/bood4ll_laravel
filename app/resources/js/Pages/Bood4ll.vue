<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import NavBar from "@/Components/NavbarComponent.vue";
import Structure from "@/Components/Structure.vue";
import TailwindIndicator from "@/Components/TailwindIndicator.vue";
import { Head, Link } from "@inertiajs/vue3";

withDefaults(
    defineProps<{
        build: any;
        tabs?: boolean;
        guest?: boolean;
    }>(),
    {
        tabs: true,
        guest: false,
    },
);
</script>

<template>
    <Head :title="$t(build[0].label || $page.props.appName)" />

    <div class="card relative min-h-screen">
        <nav v-if="guest !== true" class="sm:sticky sm:top-0 z-[10]">
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
            <NavBar class="max-w-7xl mx-auto py-1 px-2 sm:px-6 lg:px-8" />
        </nav>
        <div
            :class="{
                'max-w-7xl mx-auto py-4 px-2 sm:px-6 lg:px-8 space-y-6':
                    guest === false,
                'min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0':
                    guest !== false,
            }"
        >
            <div v-if="guest === true" class="pb-5">
                <Link href="/">
                    <ApplicationLogo
                        class="w-20 h-20 fill-current text-zero-light dark:text-zero-dark"
                    />
                </Link>
            </div>
            <Structure :build="build" :tabs="tabs" />
        </div>
        <Toast />
        <DynamicDialog />
        <ConfirmDialog group="dialog" :draggable="false" />
        <ConfirmPopup group="popup" />
        <ScrollTop />
        <TailwindIndicator />
    </div>
</template>
