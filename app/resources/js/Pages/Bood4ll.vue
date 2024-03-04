<script setup lang="ts">
import ChangeUser from "@/Components/ChangeUser.vue";
import NavBar from "@/Components/NavBar.vue";
import Structure from "@/Components/Structure.vue";
import TailwindIndicator from "@/Components/TailwindIndicator.vue";

const props = withDefaults(
    defineProps<{
        structure: any;
        tabs?: boolean;
    }>(),
    {
        tabs: true,
    },
);
</script>

<template>
    <div class="card relative min-h-screen">
        <nav class="sm:sticky sm:top-0 z-[10]">
            <Card :pt="{ body: 'p-0' }">
                <template #content>
                    <ChangeUser v-if="$page.props.auth.previousUser === true" />
                    <NavBar
                        class="max-w-7xl mx-auto py-1 px-2 sm:px-6 lg:px-8"
                    />
                </template>
            </Card>
        </nav>
        <div class="max-w-7xl mx-auto py-4 px-2 sm:px-6 lg:px-8 space-y-6">
            <Structure :structure="structure" :tabs="tabs" />
        </div>
    </div>
    <Toast />
    <DynamicDialog />
    <ConfirmDialog group="dialog" :draggable="false" />
    <ConfirmPopup group="popup" />
    <ScrollTop />
    <TailwindIndicator />
</template>
