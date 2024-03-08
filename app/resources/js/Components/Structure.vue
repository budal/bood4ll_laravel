<script setup lang="ts">
import Build from "@/Components/Build.vue";

withDefaults(
    defineProps<{
        structure?: any;
        tabs?: boolean;
        id?: string | number;
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

    <template v-if="structure.length > 1 && tabs == true">
        <TabView>
            <template v-for="item in structure">
                <TabPanel
                    :header="$t(item.label)"
                    :pt="{ content: { class: '-mx-4' } }"
                >
                    <Card>
                        <template v-if="item.label" #title>
                            {{ $t(item.label) }}
                        </template>
                        <template v-if="item.description" #subtitle>
                            {{ $t(item.description) }}
                        </template>
                        <template #content>
                            <Build :component="item" :id="id" />
                        </template>
                    </Card>
                </TabPanel>
            </template>
        </TabView>
    </template>
    <template v-else>
        <Card v-for="item in structure" class="mb-5">
            <template v-if="item.label" #title>
                {{ $t(item.label) }}
            </template>
            <template v-if="item.description" #subtitle>
                {{ $t(item.description) }}
            </template>
            <template #content>
                <Build :component="item" :id="id" />
            </template>
        </Card>
    </template>
</template>
