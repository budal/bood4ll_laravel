<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Button from '@/Components/Button.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="$t('Confirm Password')" />

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ $t('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" :value="$t('Password')" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex justify-end mt-4">
                <Button color="primary" class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ $t('Confirm') }}
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
