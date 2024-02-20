<script setup lang="ts">
import { Icon } from "@iconify/vue";
import { ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import Avatar from "primevue/avatar";
import Badge from "primevue/badge";
import Breadcrumb from "primevue/breadcrumb";
import InputText from "primevue/inputtext";
import Menubar from "primevue/menubar";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import ToggleTheme from "@/Components/ToggleTheme.vue";

import Menu from "primevue/menu";
import Button from "primevue/button";

const routeCurrent = window.location.href;

const menu = ref();

const toggle = (event: MouseEvent) => {
    menu.value.toggle(event);
};
</script>

<template>
    <div>
        <Menubar :model="$page.props.appNavMenu as any">
            <template #start>
                <ApplicationLogo
                    class="block h-9 mr-5 w-auto fill-current text-zero-light dark:text-zero-dark"
                />
            </template>
            <template #item="{ item, props, hasSubmenu, root }" class="ml-5">
                <component
                    v-if="
                        route().has(item.route) ||
                        (!route().has(item.route) &&
                            (item.items ?? []).length !== 0)
                    "
                    :is="route().has(item.route) ? Link : 'a'"
                    :href="route().has(item.route) ? route(item.route) : '#'"
                    class="flex align-items-center"
                    v-bind="props.action"
                    v-ripple
                >
                    <Icon :icon="item.icon as string" class="h-5 w-5 mr-1" />
                    <span class="ml-2 text-sm">{{
                        $t(item.label as string)
                    }}</span>
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
                <div class="xs:-mr-2 flex items-center sm:ml-6">
                    <ToggleTheme />
                    <button @click="toggle">
                        <Avatar
                            :label="
                                $page.props.auth.user.name
                                    .charAt(0)
                                    .toUpperCase()
                            "
                            shape="circle"
                            size="large"
                            class="shadow-[0_2px_10px]"
                        />
                    </button>

                    <Menu
                        ref="menu"
                        id="overlay_menu"
                        :model="$page.props.appUserMenu as any"
                        :popup="true"
                        class="text-sm"
                    >
                        <template #start>
                            <span
                                class="inline-flex align-items-center gap-1 px-2 py-2"
                            >
                                <Avatar
                                    image="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png"
                                    class="mr-2"
                                    shape="circle"
                                />
                                <span class="">
                                    <p class="font-bold">
                                        {{ $page.props.auth.user.name }}
                                    </p>
                                    <p class="text-xs">
                                        {{ $page.props.auth.user.email }}
                                    </p>
                                </span>
                            </span>
                        </template>
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
                                :is="route().has(item.route) ? Link : 'a'"
                                :href="
                                    route().has(item.route)
                                        ? route(item.route)
                                        : '#'
                                "
                                :method="item.method"
                                class="flex align-items-center"
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
                </div>
            </template>
        </Menubar>
    </div>
</template>
