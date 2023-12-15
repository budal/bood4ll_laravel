<script setup lang="ts">
import Button from "@/Components/Button.vue";
</script>

<template>
    <Button
        v-if="$page.props.auth.previousUser === true"
        color="warning"
        start-icon="mdi:account-convert"
        rounded="rounded-full"
        class="fixed top-[100px] right-1"
        link="apps.users.return_to_my_user"
        method="post"
    />
</template>
