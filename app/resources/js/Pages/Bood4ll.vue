<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import NavBar from "@/Components/NavBar.vue";
import TailwindIndicator from "@/Components/TailwindIndicator.vue";

import Table from "@/Components/Table.vue";

const props = withDefaults(
    defineProps<{
        component?: any;
        data?: any;
    }>(),
    {},
);
</script>

<template>
    <div class="card relative min-h-screen">
        <nav
            class="bg-zero-light dark:bg-zero-dark sm:sticky sm:top-0 z-[10] border-b"
        >
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
        <div class="max-w-7xl mx-auto pt-8 px-2 sm:px-6 lg:px-8 space-y-6">
            <Table :component="component" :data="data" />
        </div>
    </div>
    <Toast />
    <DynamicDialog />
    <ConfirmDialog group="dialog" :draggable="false" />
    <ConfirmPopup group="popup" />
    <ScrollTop />
    <TailwindIndicator />
</template>
