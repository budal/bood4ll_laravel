<script setup lang="ts">
import { computed } from "vue";
import { Link, Head, usePage } from "@inertiajs/vue3";

const insertBetween = (items: any, insertion: string) => {
    return items.flatMap((value: any, index: number, array: string | any[]) =>
        array.length - 1 !== index ? [value, insertion] : value,
    );
};

const breadcrumbs = computed(() =>
    insertBetween(usePage().props.breadcrumbs || [], "/"),
);
</script>

<template>
    <Head
        :title="
            $t(
                breadcrumbs[breadcrumbs.length - 1]?.title ||
                    usePage().props.appName,
            )
        "
    />

    <header
        v-if="breadcrumbs.length > 0"
        class="bg-zero-light dark:bg-zero-dark shadow-primary-light/20 dark:shadow-primary-dark/20 border-t border-zero-light dark:border-zero-dark shadow-[0_2px_10px]"
    >
        <div class="flex gap-4 max-w-7xl mx-auto py-1 px-4 sm:px-6 lg:px-8 ">
            <h2
                v-for="page in breadcrumbs"
                class="font-semibold text-md text-zero-light dark:text-zero-dark leading-tight"
            >
                <span
                    v-if="page === '/'"
                    class="text-zero-light/50 dark:text-zero-dark/50"
                    >/</span
                >
                <template v-else>
                    <Link
                        v-if="page.current !== true"
                        :href="page.url"
                        :disabled="page.current"
                        class="border-b-2 focus:outline-none border-transparent hover:border-zero-dark dark:hover:border-zero-light focus:border-zero-dark dark:focus:border-zero-light transition ease-in-out duration-500"
                        as="button"
                    >
                        {{ $t(page.title) }}
                    </Link>
                    <span v-else>
                        {{ $t(page.title) }}
                    </span>
                </template>
            </h2>
        </div>
    </header>
</template>
