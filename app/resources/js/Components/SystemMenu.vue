<script setup lang="ts">
    import { ref } from 'vue'
    import {
        DropdownMenuArrow,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuPortal,
        DropdownMenuRoot,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
    } from 'radix-vue'
    import { Link } from '@inertiajs/vue3';
    import Avatar from '@/Components/Avatar.vue';

    defineProps<{
        content: any;
    }>();

const toggleState = ref(false)
    const checkboxOne = ref(false)
</script>

<template>
    <DropdownMenuRoot v-model:open="toggleState">
        <DropdownMenuTrigger
            class="rounded-full focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-1 focus:ring-offset-primary-light dark:focus:ring-offset-primary-dark shadow-[0_2px_10px] shadow-black dark:shadow-white focus:shadow-[0_0_0_2px] focus:shadow-black dark:focus:shadow-white transition ease-in-out duration-500"
            aria-label="Customise options"
        >
            <slot name="trigger" />
        </DropdownMenuTrigger>

        <DropdownMenuPortal>
            <DropdownMenuContent
                class="min-w-[220px] outline-none bg-secondary-light dark:bg-secondary-dark text-primary-dark dark:text-primary-light rounded-md shadow-[0px_10px_38px_-10px_rgba(22,_23,_24,_0.35),_0px_10px_20px_-15px_rgba(22,_23,_24,_0.2)] shadow-black dark:shadow-white will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
                :side-offset="10"
                :align="'end'"
            >
                <DropdownMenuLabel class="leading-[25px] text-center">
                    <div class="pt-2">
                        <Avatar class="place-items-center h-20 w-20" :fallback="$page.props.auth.user.name" />
                    </div>
                    <div class="pt-2 font-sm text-sm text-gray-800 dark:text-gray-200">{{ $page.props.auth.user.name }}</div>
                    <div class="font-xs text-xs text-gray-500">{{ $page.props.auth.user.email }}</div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator class="h-[0.5px] my-[5px] bg-system-light dark:bg-system-dark" />
                <template v-for="item in content">
                    <Link v-if="item.title != '-'" :href="item.route"> 
                        <DropdownMenuItem
                            :value="item.title"
                            class="px-[5px] relative pl-[25px] text-sm py-3 text-secondary-light dark:text-secondary-dark hover:bg-system-light dark:hover:bg-system-dark focus:outline-none focus:bg-system-light dark:focus:bg-system-dark transition duration-150 ease-in-out"
                        >
                            {{ $t(item.title) }} 
                        </DropdownMenuItem>
                    </Link>
                    <DropdownMenuSeparator v-else class="h-[0.5px] my-[5px] bg-system-light dark:bg-system-dark" />
                </template>
                <DropdownMenuSeparator class="h-[0.5px] my-[5px] bg-system-light dark:bg-system-dark" />
                <Link class="w-full text-left" :href="route('logout')" method="post" as="button">
                    <DropdownMenuItem
                        value="Logout"
                        class="px-[5px] relative pl-[25px] text-sm py-3 text-secondary-light dark:text-secondary-dark hover:bg-system-light dark:hover:bg-system-dark focus:outline-none focus:bg-system-light dark:focus:bg-system-dark transition duration-150 ease-in-out"
                    >
                        {{ $t('Log Out') }}
                    </DropdownMenuItem>
                </Link>
                <DropdownMenuArrow class="fill-white" />
            </DropdownMenuContent>
        </DropdownMenuPortal>
    </DropdownMenuRoot>
</template>