<script setup lang="ts">
    import { ref } from 'vue'
    import { Icon } from '@iconify/vue'
    import {
        DropdownMenuArrow,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuPortal,
        DropdownMenuRoot,
        DropdownMenuSeparator,
        DropdownMenuSub,
        DropdownMenuSubContent,
        DropdownMenuSubTrigger,
        DropdownMenuTrigger,
    } from 'radix-vue'
    import { Link } from '@inertiajs/vue3';
    import Avatar from '@/Components/Avatar.vue';

    defineProps<{
        content: any;
    }>();

    const toggleState = ref(false)
</script>

<template>
    <DropdownMenuRoot v-model:open="toggleState">
        <DropdownMenuTrigger
            class="rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark shadow-[0_2px_10px] shadow-black dark:shadow-white focus:shadow-[0_0_0_2px] focus:shadow-black dark:focus:shadow-white transition ease-in-out duration-500"
            :aria-label="$t('User menu')"
        >
            <slot name="trigger" />
        </DropdownMenuTrigger>

        <DropdownMenuPortal>
            <DropdownMenuContent
                class="z-[20] min-w-[220px] overflow-hidden outline-none bg-secondary-light dark:bg-secondary-dark secondary-light dark:text-secondary-dark rounded-md shadow-[0px_10px_38px_-10px_rgba(22,_23,_24,_0.35),_0px_10px_20px_-15px_rgba(22,_23,_24,_0.2)] shadow-black dark:shadow-white will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
                :align="'end'"
            >
                <DropdownMenuLabel as="span" class="leading-[25px] text-center">
                    <div class="pt-2 hidden sm:block">
                        <Avatar class="place-items-center h-20 w-20" :fallback="$page.props.auth.user.name" />
                    </div>
                    <div class="pt-2 font-sm text-sm text-secondary-light dark:text-secondary-dark">{{ $page.props.auth.user.name }}</div>
                    <div class="font-xs text-xs text-secondary-light/70 dark:text-secondary-dark/70">{{ $page.props.auth.user.email }}</div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator class="h-[0.5px] mt-2 bg-zero-dark/10 dark:bg-zero-light/5" />
                <template v-for="item in content">
                    <template v-if="item.links && item.title != '-'">
                        <DropdownMenuSub>
                            <DropdownMenuSubTrigger
                                :value="item.title"
                                class="flex items-center px-[5px] relative pl-[25px] text-sm py-3 text-secondary-light dark:text-secondary-dark hover:bg-zero-light dark:hover:bg-zero-dark focus:outline-none focus:bg-zero-light dark:focus:bg-zero-dark transition duration-150 ease-in-out"
                                :class="item.class"
                            >
                                {{ $t(item.title) }}
                                <div
                                    class="ml-auto pl-[20px] text-mauve11 group-data-[highlighted]:text-white group-data-[disabled]:opacity-25"
                                >
                                    <Icon icon="radix-icons:chevron-right" />
                                </div>
                            </DropdownMenuSubTrigger>
                            <DropdownMenuPortal>
                                <DropdownMenuSubContent
                                    class="w-[220px] overflow-hidden outline-none bg-secondary-light dark:bg-secondary-dark text-secondary-light dark:secondary-dark rounded-md shadow-[0px_10px_38px_-10px_rgba(22,_23,_24,_0.35),_0px_10px_20px_-15px_rgba(22,_23,_24,_0.2)] shadow-black dark:shadow-white will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
                                    :side="'top'"
                                >
                                    <template v-for="subitem in item.links">
                                        <Link v-if="subitem.title != '-'" :href="route(subitem.route)">
                                            <DropdownMenuItem
                                                :value="item.title"
                                                class="group items-center px-[5px] relative pl-[25px] text-sm py-3 text-zero-light dark:text-zero-dark hover:bg-zero-light dark:hover:bg-zero-dark focus:outline-none focus:bg-zero-light dark:focus:bg-zero-dark transition duration-150 ease-in-out"
                                            >
                                                {{ $t(subitem.title) }}
                                                <div class="font-xs text-xs text-zero-light/70 dark:text-zero-dark/70">{{ $t(subitem.description) }}</div>
                                            </DropdownMenuItem>
                                        </Link>
                                        <DropdownMenuSeparator v-else class="h-[0.5px] bg-zero-dark/10 dark:bg-zero-light/5" :class="item.class" />
                                    </template>
                                </DropdownMenuSubContent>
                            </DropdownMenuPortal>
                            </DropdownMenuSub>
                    </template>
                    <template v-else>
                        <Link v-if="item.title != '-'" :href="route(item.route)"> 
                            <DropdownMenuItem
                                :value="item.title"
                                :class="item.class"
                                class="px-[5px] relative pl-[25px] text-sm py-3 text-zero-light dark:text-zero-dark hover:bg-zero-light dark:hover:bg-zero-dark focus:outline-none focus:bg-zero-light dark:focus:bg-zero-dark transition duration-150 ease-in-out"
                            >
                                {{ $t(item.title) }} 
                            </DropdownMenuItem>
                        </Link>
                        <DropdownMenuSeparator v-else class="h-[0.5px] bg-zero-dark/10 dark:bg-zero-light/5" :class="item.class" />
                    </template>
                </template>
                <DropdownMenuSeparator class="h-[0.5px] bg-zero-dark/10 dark:bg-zero-light/5" />
                <Link class="w-full text-left" :href="route('logout')" method="post" as="button">
                    <DropdownMenuItem
                        value="Logout"
                        class="px-[5px] relative pl-[25px] text-sm py-3 text-zero-light dark:text-zero-dark hover:bg-zero-light dark:hover:bg-zero-dark focus:outline-none focus:bg-zero-light dark:focus:bg-zero-dark transition duration-150 ease-in-out"
                    >
                        {{ $t('Log Out') }}
                    </DropdownMenuItem>
                </Link>
                <DropdownMenuArrow class="fill-secondary-light dark:fill-secondary-dark" />
            </DropdownMenuContent>
        </DropdownMenuPortal>
    </DropdownMenuRoot>
</template>