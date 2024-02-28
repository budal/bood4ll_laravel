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

    <Card v-if="component.length > 1 && tabs == true">
        <template #content>
            <TabView>
                <TabPanel v-for="item in component" :header="$t(item.label)">
                    <Build :component="item">
                        <template #description>
                            {{ $t(item.description) }}
                        </template>
                    </Build>
                </TabPanel>
            </TabView>
        </template>
    </Card>

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
