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
    body: any;
    data?: any;
}>();    

const form = useForm({
    uuid: props.data?.uuid || '',
    name: props.data?.name || '',
    email: props.data?.email || '',
    username: props.data?.username || '',
});

const update = (uuid : string) => {
    toast.success('Wow so easy !');

    // nextTick(() => passwordInput.value?.focus());

    if (uuid) {
        form.patch(route('apps.users.update', props.data.uuid), {
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
    <section v-for="body in props.body">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $t(body.title) }}</h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $t(body.subtitle) }}
            </p>
        </header>
        
        <form @submit.prevent="update(form.uuid)" class="space-y-6 mt-2">
            <div class="flex flex-col">
                <div v-for="group in body.fields" :class="`grid sm:grid-cols-${body.cols} sm:gap-4`">
                    <div v-for="field in group" :class="`${field.span ? `sm:col-span-${field.span}` : ''} mt-4`">
                        <InputLabel :for="field.name" :value="$t(field.title)" />
        
                        <TextInput
                            :id="field.name"
                            :type="field.type"
                            class="mt-1 block w-full"
                            v-model="form[field.name]"
                            required
                            autofocus
                            :autocomplete="field.name"
                        />
        
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                </div>
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
