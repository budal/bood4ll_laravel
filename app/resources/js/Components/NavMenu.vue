<script setup lang="ts">
import { Icon } from "@iconify/vue";
import { ref } from "vue";
import {
    NavigationMenuContent,
    NavigationMenuIndicator,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuRoot,
    NavigationMenuTrigger,
    NavigationMenuViewport,
} from "radix-vue";
import { Link } from "@inertiajs/vue3";

import MegaMenu from "primevue/megamenu";

defineProps<{
    content: any;
}>();

const currentTrigger = ref("");
const routeCurrent = window.location.href;

const items = ref([
    {
        label: "Home",
        icon: "pi pi-home",
    },
    {
        label: "Furniture",
        icon: "pi pi-box",
        items: [
            [
                {
                    label: "Living Room",
                    items: [
                        { label: "Accessories" },
                        { label: "Armchair" },
                        { label: "Coffee Table" },
                        { label: "Couch" },
                        { label: "TV Stand" },
                    ],
                },
            ],
            [
                {
                    label: "Kitchen",
                    items: [
                        { label: "Bar stool" },
                        { label: "Chair" },
                        { label: "Table" },
                    ],
                },
                {
                    label: "Bathroom",
                    items: [{ label: "Accessories" }],
                },
            ],
            [
                {
                    label: "Bedroom",
                    items: [
                        { label: "Bed" },
                        { label: "Chaise lounge" },
                        { label: "Cupboard" },
                        { label: "Dresser" },
                        { label: "Wardrobe" },
                    ],
                },
            ],
            [
                {
                    label: "Office",
                    items: [
                        { label: "Bookcase" },
                        { label: "Cabinet" },
                        { label: "Chair" },
                        { label: "Desk" },
                        { label: "Executive Chair" },
                    ],
                },
            ],
        ],
    },
    {
        label: "Electronics",
        icon: "pi pi-mobile",
        items: [
            [
                {
                    label: "Computer",
                    items: [
                        { label: "Monitor" },
                        { label: "Mouse" },
                        { label: "Notebook" },
                        { label: "Keyboard" },
                        { label: "Printer" },
                        { label: "Storage" },
                    ],
                },
            ],
            [
                {
                    label: "Home Theather",
                    items: [
                        { label: "Projector" },
                        { label: "Speakers" },
                        { label: "TVs" },
                    ],
                },
            ],
            [
                {
                    label: "Gaming",
                    items: [
                        { label: "Accessories" },
                        { label: "Console" },
                        { label: "PC" },
                        { label: "Video Games" },
                    ],
                },
            ],
            [
                {
                    label: "Appliances",
                    items: [
                        { label: "Coffee Machine" },
                        { label: "Fridge" },
                        { label: "Oven" },
                        { label: "Vaccum Cleaner" },
                        { label: "Washing Machine" },
                    ],
                },
            ],
        ],
    },
    {
        label: "Sports",
        icon: "pi pi-clock",
        items: [
            [
                {
                    label: "Football",
                    items: [
                        { label: "Kits" },
                        { label: "Shoes" },
                        { label: "Shorts" },
                        { label: "Training" },
                    ],
                },
            ],
            [
                {
                    label: "Running",
                    items: [
                        { label: "Accessories" },
                        { label: "Shoes" },
                        { label: "T-Shirts" },
                        { label: "Shorts" },
                    ],
                },
            ],
            [
                {
                    label: "Swimming",
                    items: [
                        { label: "Kickboard" },
                        { label: "Nose Clip" },
                        { label: "Swimsuits" },
                        { label: "Paddles" },
                    ],
                },
            ],
            [
                {
                    label: "Tennis",
                    items: [
                        { label: "Balls" },
                        { label: "Rackets" },
                        { label: "Shoes" },
                        { label: "Training" },
                    ],
                },
            ],
        ],
    },
]);
</script>

<template>
    <div>
        <MegaMenu :model="items" />

        <NavigationMenuRoot
            v-model="currentTrigger"
            class="z-[100] flex w-full justify-center"
        >
            <NavigationMenuList
                class="center m-0 flex list-none rounded-[6px] p-1"
            >
                <template v-for="item in content">
                    <template v-if="item.links">
                        <NavigationMenuItem v-if="item.links.length !== 0">
                            <NavigationMenuTrigger
                                class="group flex items-center justify-between gap-[2px] mx-3 py-[19px] sm:py-[23px] leading-none outline-none border-b-2 border-transparent select-none text-sm font-medium transition ease-in-out duration-500"
                                :class="
                                    routeCurrent.includes(item.route)
                                        ? 'border-warning-light dark:border-warning-dark focus:border-info-light dark:focus:border-info-dark text-zero-light dark:text-zero-dark'
                                        : 'hover:border-zero-dark dark:hover:border-zero-light focus:border-zero-dark dark:focus:border-zero-light text-zero-light/60 dark:text-zero-dark/60 hover:text-zero-light dark:hover:text-zero-dark focus:text-zero-light dark:focus:text-zero-dark'
                                "
                            >
                                <Icon
                                    :icon="item.icon"
                                    class="flex w-5 h-5 sm:hidden text-zero-light/60 dark:text-zero-dark/60 group-hover:text-zero-light dark:group-hover:text-zero-dark focus:text-zero-light dark:focus:text-zero-dark relative top-[1px]"
                                    aria-hidden
                                />
                                <span
                                    class="hidden sm:flex text-zero-light/60 dark:text-zero-dark/60 group-hover:text-zero-light dark:group-hover:text-zero-dark focus:text-zero-light dark:focus:text-zero-dark relative top-[1px]"
                                >
                                    {{ $t(item.title) }}
                                </span>
                                <Icon
                                    icon="radix-icons:caret-down"
                                    class="text-zero-light/60 dark:text-zero-dark/60 group-hover:text-zero-light dark:group-hover:text-zero-dark focus:text-zero-light dark:focus:text-zero-dark relative top-[1px] transition-transform duration-[250] ease-in group-data-[state=open]:-rotate-180"
                                    aria-hidden
                                />
                            </NavigationMenuTrigger>
                            <NavigationMenuContent
                                class="z-[30] absolute top-0 left-0 bg-zero-light-hover dark:bg-zero-dark-hover w-full sm:w-auto data-[motion=from-start]:animate-enterFromLeft data-[motion=from-end]:animate-enterFromRight data-[motion=to-start]:animate-exitToLeft data-[motion=to-end]:animate-exitToRight"
                            >
                                <div
                                    class="container mx-auto grid gap-2 p-2 bg-zero-light-hover dark:bg-zero-dark-hover w-screen max-w-xl md:max-w-3xl lg:max-w-5xl sm:grid-flow-row sm:grid-cols-2 lg:grid-cols-3"
                                >
                                    <template v-for="subitem in item.links">
                                        <NavigationMenuLink as-child>
                                            <Link
                                                :href="route(subitem.route)"
                                                class="border-transparent bg-zero-light dark:bg-zero-dark hover:bg-zero-light-hover dark:hover:bg-zero-dark-hover focus:bg-zero-light-hover dark:focus:bg-zero-dark-hover block select-none rounded-[6px] p-3 text-[15px] leading-none no-underline shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px] outline-none transition-colors duration-500"
                                                :class="
                                                    routeCurrent.includes(
                                                        route(subitem.route),
                                                    )
                                                        ? 'ring-2 focus-visible:ring ring-warning-light dark:ring-warning-dark'
                                                        : ''
                                                "
                                            >
                                                <div
                                                    class="text-zero-light dark:text-zero-dark mb-[5px] font-medium leading-[1.2]"
                                                >
                                                    {{ $t(subitem.title) }}
                                                </div>
                                                <p
                                                    class="text-xs text-zero-light/60 dark:text-zero-dark/60 my-0 leading-[1.4]"
                                                >
                                                    {{
                                                        $t(subitem.description)
                                                    }}
                                                </p>
                                            </Link>
                                        </NavigationMenuLink>
                                    </template>
                                </div>
                            </NavigationMenuContent>
                        </NavigationMenuItem>
                    </template>
                    <template v-else>
                        <NavigationMenuItem>
                            <Link
                                :href="route($t(item.route))"
                                class="group text-sm font-medium block select-none mx-3 py-[19px] sm:py-[23px] leading-none no-underline outline-none border-b-2 border-transparent transition ease-in-out duration-500"
                                :class="
                                    routeCurrent.includes(item.route)
                                        ? 'border-warning-light dark:border-warning-dark focus:border-info-light dark:focus:border-info-dark text-zero-light dark:text-zero-dark '
                                        : 'hover:border-zero-dark dark:hover:border-zero-light focus:border-zero-dark dark:focus:border-zero-light text-zero-light/60 dark:text-zero-dark/60 hover:text-zero-light dark:hover:text-zero-dark focus:text-zero-light dark:focus:text-zero-dark'
                                "
                            >
                                <Icon
                                    :icon="item.icon"
                                    class="flex w-5 h-5 sm:hidden text-zero-light/60 dark:text-zero-dark/60 group-hover:text-zero-light dark:group-hover:text-zero-dark focus:text-zero-light dark:focus:text-zero-dark relative top-[1px]"
                                    aria-hidden
                                />
                                <span
                                    class="hidden sm:flex text-zero-light/60 dark:text-zero-dark/60 group-hover:text-zero-light dark:group-hover:text-zero-dark focus:text-zero-light dark:focus:text-zero-dark relative top-[1px]"
                                >
                                    {{ $t(item.title) }}
                                </span>
                            </Link>
                        </NavigationMenuItem>
                    </template>
                </template>
                <NavigationMenuIndicator
                    class="data-[state=hidden]:opacity-0 duration-200 data-[state=visible]:animate-fadeIn data-[state=hidden]:animate-fadeOut top-full z-[1] flex h-[10px] items-end justify-center overflow-hidden transition-[all,transform_250ms_ease]"
                >
                    <div
                        class="relative top-[10%] h-[10px] w-[10px] rotate-[45deg] rounded-tl-[2px] bg-zero-light-hover dark:bg-zero-dark-hover"
                    />
                </NavigationMenuIndicator>
            </NavigationMenuList>
            <div
                class="perspective-[2000px] absolute top-full left-0 flex w-full justify-center"
            >
                <NavigationMenuViewport
                    class="bg-zero-light-hover dark:bg-zero-dark-hover data-[state=open]:animate-scaleIn data-[state=closed]:animate-scaleOut relative -mt-[19px] h-[var(--radix-navigation-menu-viewport-height)] w-full origin-[top_center] overflow-hidden rounded-[10px] transition-[width,_height] duration-300 sm:w-[var(--radix-navigation-menu-viewport-width)]"
                />
            </div>
        </NavigationMenuRoot>
    </div>
</template>
