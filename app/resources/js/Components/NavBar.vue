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

import Menu from "primevue/menu";
import Button from "primevue/button";

defineProps<{
    items: any;
}>();

const routeCurrent = window.location.href;
</script>

<template>
    <div>
        <Menubar :model="items">
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
                <div class="flex align-items-center gap-2">
                    <Avatar
                        :label="
                            $page.props.auth.user.name.charAt(0).toUpperCase()
                        "
                        shape="circle"
                        size="large"
                    />
                </div>
            </template>
        </Menubar>
    </div>
</template>
