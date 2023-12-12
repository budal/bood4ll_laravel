<script setup lang="ts">
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Button from "@/Components/Button.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <GuestLayout>
        <Head :title="$t('Forgot your password?')" />

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{
                $t(
                    "Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.",
                )
            }}
        </div>

        <div
            v-if="status"
            class="mb-4 font-medium text-sm text-green-600 dark:text-green-400"
        >
            {{ $t(status) }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" :value="$t('Email')" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Button
                    color="primary"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ $t("Email Password Reset Link") }}
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
