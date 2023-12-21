<template>
    <span class="relative inline-flex">
        <slot />
        <span class="flex absolute h-3 w-3 top-0 right-0 -mt-1 -mr-1">
            <span
                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-danger-dark opacity-75"
            ></span>
            <span
                class="relative inline-flex rounded-full h-3 w-3 bg-danger-light text-danger-light text-xs"
            ></span>
        </span>
    </span>
</template>
