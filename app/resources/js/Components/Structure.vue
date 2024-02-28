<script setup lang="ts">
import Build from "@/Components/Build.vue";

withDefaults(
    defineProps<{
        component?: any;
        tabs: boolean;
        status?: string;
        statusTheme?: string;
    }>(),
    {
        statusTheme: "info",
    },
);
</script>

<template>
    <Message v-if="status" :severity="statusTheme">
        {{ $t(status) }}
    </Message>

    <TabView v-if="component.length > 1 && tabs == true">
        <TabPanel v-for="item in component" :header="$t(item.label)">
            <Build :component="item" />
        </TabPanel>
    </TabView>

    <template v-else>
        <Card v-for="item in component">
            <template v-if="item.label" #title>
                {{ $t(item.label) }}
            </template>
            <template v-if="item.description" #subtitle>
                {{ $t(item.description) }}
            </template>
            <template #content>
                <Build :component="item" />
            </template>
        </Card>
    </template>
</template>
