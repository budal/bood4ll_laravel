<template>
    <div
        v-if="$page.props.appName !== 'production'"
        className="fixed z-[20] bottom-1 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-primary-light dark:bg-primary-dark p-3 font-mono text-xs text-primary-light dark:text-primary-dark"
    >
        <div className="block sm:hidden">xs</div>
        <div className="hidden sm:block md:hidden">sm</div>
        <div className="hidden md:block lg:hidden">md</div>
        <div className="hidden lg:block xl:hidden">lg</div>
        <div className="hidden xl:block 2xl:hidden">xl</div>
        <div className="hidden 2xl:block">2xl</div>
    </div>
</template>
