<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
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
</template>
