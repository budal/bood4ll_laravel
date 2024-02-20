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

defineProps<{
    content: any;
}>();

const home = ref({
    icon: "pi pi-home",
    route: "/introduction",
});
const items = ref([
    { label: "Components" },
    { label: "Form" },
    { label: "InputText", route: "/inputtext" },
]);
</script>

<template>
    <Breadcrumb :home="home" :model="items" class="text-xs">
        <template #item="{ item, props }">
            <router-link
                v-if="item.route"
                v-slot="{ href, navigate }"
                :to="item.route"
                custom
            >
                <a :href="href" v-bind="props.action" @click="navigate">
                    <span :class="[item.icon, 'text-color']" />
                    <span class="text-primary font-semibold">{{
                        item.label
                    }}</span>
                </a>
            </router-link>
            <a
                v-else
                :href="item.url"
                :target="item.target"
                v-bind="props.action"
            >
                <span class="text-color">{{ item.label }}</span>
            </a>
        </template>
    </Breadcrumb>
</template>
