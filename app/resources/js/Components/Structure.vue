<script setup lang="ts">
import Build from "@/Components/Build.vue";

withDefaults(
    defineProps<{
        structure?: any;
        tabs?: boolean;
        buildRoute?: any;
    }>(),
    {
        tabs: true,
    },
);
</script>

<template>
    <Message
        v-if="$page.props.status"
        :severity="($page.props.statusTheme as string) || 'info'"
    >
        {{ $t($page.props.status as string) }}
    </Message>

    <Card v-if="structure.length > 1 && tabs == true">
        <template #content>
            <TabView>
                <template v-for="item in structure">
                    <TabPanel :header="$t(item.label)">
                        <Build :component="item" :buildRoute="buildRoute">
                            <template v-if="item.description" #description>
                                {{ $t(item.description) }}
                            </template>
                        </Build>
                    </TabPanel>
                </template>
            </TabView>
        </template>
    </Card>

    <template v-else>
        <Card v-for="item in structure">
            <template v-if="item.label" #title>
                {{ $t(item.label) }}
            </template>
            <template v-if="item.description" #subtitle>
                {{ $t(item.description) }}
            </template>
            <template #content>
                <Build :component="item" :buildRoute="buildRoute" />
            </template>
        </Card>
    </template>
</template>
