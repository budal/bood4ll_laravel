<template>
    <span class="relative inline-flex">
        <slot />
        <span class="flex absolute h-3 w-3 top-0 right-0 -mt-1 -mr-1">
            <span
                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-info-light bg-info-dark opacity-75"
            ></span>
            <span
                class="relative inline-flex rounded-full h-3 w-3 bg-info-light text-info-light text-xs"
            ></span>
        </span>
    </span>
</template>
