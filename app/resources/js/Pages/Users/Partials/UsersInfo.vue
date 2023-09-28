<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { toast } from 'vue3-toastify';

const passwordInput = ref<HTMLInputElement | null>(null);

const props = defineProps<{
    data?: any;
}>();    

const form = useForm({
    id: props.data?.id || '',
    name: props.data?.name || '',
    email: props.data?.email || '',
});

const update = (id : string) => {
    toast.success('Wow so easy !');

    // nextTick(() => passwordInput.value?.focus());

    if (id) {
        form.patch(route('apps.users.update', props.data.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
            },
            onError: () => {
                // if (form.errors.password) {
                //     form.reset('password', 'password_confirmation');
                //     passwordInput.value?.focus();
                // }
                // if (form.errors.current_password) {
                //     form.reset('current_password');
                //     currentPasswordInput.value?.focus();
                // }
            },
            onFinish: () => {
                // form.reset('password', 'password_confirmation');
            },
        });
    } else {
        form.post(route('apps.users.create'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
            },
            onError: () => {
                // if (form.errors.password) {
                //     form.reset('password', 'password_confirmation');
                //     passwordInput.value?.focus();
                // }
                // if (form.errors.current_password) {
                //     form.reset('current_password');
                //     currentPasswordInput.value?.focus();
                // }
            },
            onFinish: () => {
                // form.reset('password', 'password_confirmation');
            },
        });
    }
};

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $t('Profile Information') }}</h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $t("Update your account's profile information and email address.") }}
            </p>
        </header>

        <form @submit.prevent="update(form.id)" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" :value="$t('Name')" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" :value="$t('Email')" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton 
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">{{ $t('Save') }}
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">{{ $t('Saved.') }}</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
