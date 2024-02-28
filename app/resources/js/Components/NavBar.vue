<script setup lang="ts">
import { ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";

import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const menu = ref(false);
</script>

<template>
    <div>
        <Head
            :title="
                $t(
                    $page.props.breadcrumbs[$page.props.breadcrumbs.length - 1]
                        ?.title || $page.props.appName,
                )
            "
        />
        <div>
            <Menubar :model="$page.props.appNavMenu">
                <template #start>
                    <ApplicationLogo
                        class="block h-9 mr-5 w-auto fill-current text-zero-light dark:text-zero-dark"
                    />
                </template>
                <template #item="{ item, props, hasSubmenu, root }">
                    <component
                        v-if="
                            route().has(item.route) ||
                            (!route().has(item.route) &&
                                (item.items ?? []).length !== 0)
                        "
                        :is="route().has(item.route) ? Link : 'a'"
                        :href="
                            route().has(item.route) ? route(item.route) : '#'
                        "
                        class="flex align-items-center"
                        v-bind="props.action"
                        v-ripple
                    >
                        <Icon :icon="item.icon || ''" class="h-6 w-6 mr-1" />
                        <span class="ml-2">
                            {{ $t(item.label as string) }}
                        </span>
                        <Badge
                            v-if="item.badge"
                            :class="{ 'ml-auto': !root, 'ml-2': root }"
                            :value="item.badge"
                        />
                        <span
                            v-if="item.shortcut"
                            class="ml-auto border-1 surface-border border-round surface-100 text-xs p-1"
                            >{{ item.shortcut }}</span
                        >
                        <Icon
                            v-if="hasSubmenu && root"
                            icon="material-symbols-light:expand-more-rounded"
                            class="h-5 w-5"
                        />
                        <Icon
                            v-if="hasSubmenu && !root"
                            icon="material-symbols-light:chevron-right"
                            class="h-5 w-5"
                        />
                    </component>
                </template>
                <template #end>
                    <div class="xs:-mr-2 flex items-center gap-2 sm:ml-6">
                        <button @click="menu = true">
                            <Avatar
                                :label="
                                    $page.props.auth.user.name
                                        .charAt(0)
                                        .toUpperCase()
                                "
                                shape="circle"
                                size="large"
                                class="shadow-[0_2px_15px]"
                            />
                        </button>
                        <Sidebar v-model:visible="menu" position="right">
                            <template #header>
                                <span
                                    class="inline-flex align-items-center gap-1 px-2 py-2"
                                >
                                    <Avatar
                                        image="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png"
                                        class="mr-2"
                                        shape="circle"
                                    />
                                    <span>
                                        <p class="font-bold">
                                            {{ $page.props.auth.user.name }}
                                        </p>
                                        <p class="text-sm">
                                            {{ $page.props.auth.user.email }}
                                        </p>
                                    </span>
                                </span>
                            </template>
                            <Menu :model="$page.props.appUserMenu">
                                <template #start> </template>
                                <template #submenuheader="{ item }">
                                    <span class="text-primary font-bold">
                                        {{ $t(item.label as string) }}
                                    </span>
                                </template>
                                <template #item="{ item, props }">
                                    <component
                                        v-if="
                                            route().has(item.route) ||
                                            (!route().has(item.route) &&
                                                (item.items ?? []).length !== 0)
                                        "
                                        :is="
                                            route().has(item.route) ? Link : 'a'
                                        "
                                        :href="
                                            route().has(item.route)
                                                ? route(item.route)
                                                : '#'
                                        "
                                        :method="item.method"
                                        class="flex align-items-center"
                                        as="span"
                                        v-bind="props.action"
                                        v-ripple
                                    >
                                        <span :class="item.icon" />
                                        <span class="ml-2">
                                            {{ $t(item.label as string) }}
                                        </span>
                                        <Badge
                                            v-if="item.badge"
                                            class="ml-auto"
                                            :value="item.badge"
                                        />
                                        <span
                                            v-if="item.shortcut"
                                            class="ml-auto border-1 surface-border border-round surface-100 text-xs p-1"
                                        >
                                            {{ item.shortcut }}
                                        </span>
                                    </component>
                                </template>
                            </Menu>
                        </Sidebar>
                    </div>
                </template>
            </Menubar>
            <Breadcrumb
                :model="$page.props.breadcrumbs"
                class="text-sm py-1 px-10 mt-1"
            >
                <template #item="{ item, props }">
                    <Link
                        as="button"
                        v-bind="props.action"
                        :href="item.url || ''"
                        :disabled="item.current === true"
                    >
                        <Icon
                            v-if="item.icon"
                            :icon="item.icon || ''"
                            class="h-5 w-5"
                        />
                        <span
                            :class="{
                                'text-primary font-semibold':
                                    item.current === true,
                            }"
                        >
                            {{ $t(item.title) }}
                        </span>
                    </Link>
                </template>
                <template #separator> / </template>
            </Breadcrumb>
        </div>
    </div>
</template>
