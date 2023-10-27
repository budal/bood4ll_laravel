<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { onBeforeMount, reactive } from 'vue';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import { trans } from 'laravel-vue-i18n';
import Select from '@/Components/Select.vue';

const passwordInput = ref<HTMLInputElement | null>(null);

const props = defineProps<{
    method: any;
    body: any;
    data?: any;
}>();    

const url = window.location.href;

const form = useForm({});

let items = reactive(new Set())

props.body.forEach((forms: any) => {
    forms.fields.forEach((fields: any) => {
        fields.forEach((field: any) => {
            items.add(field.name);
        });
    });
});

function dynamicFields() {
    let data = reactive(new Set())

    items.forEach((field: any) => {
        data[field as never] = form[field as never] ;
    });

    return data;
}

onBeforeMount(() => {
    items.forEach((field: any) => {
        form[field] = props.data ? props.data[field] : '';
    });
})

const sendForm = () => {
    form.transform(() => ({ ...dynamicFields() }))

    form.submit(props.method, url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(trans(usePage().props.status as string));
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
    })
}

const content = [
  { id: '', title: 'Only active' },
  { id: 'only', title: 'Only trashed' },
  { id: 'with', title: 'Active and trashed' },
];

</script>

<template>
    <section v-for="body in props.body">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $t(body.title) }}</h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $t(body.subtitle) }}
            </p>
        </header>
        
        <form @submit.prevent="sendForm" class="space-y-6 mt-2">
            <div class="flex flex-col">
                <div v-for="group in body.fields" :class="`grid sm:grid-cols-${body.cols} sm:gap-4`">
                    <div v-for="field in group" :class="`${field.span ? `sm:col-span-${field.span}` : ''} mt-4`">
                        <InputLabel :for="field.name" :value="$t(field.title)" />
        
                        <TextInput v-if="field.type == 'input'"
                            :id="field.name"
                            :name="field.name"
                            :type="field.type"
                            class="mt-1 block w-full"
                            v-model="form[field.name as never]"
                            required
                            autofocus
                            :autocomplete="field.name"
                        />

                        <Select v-if="field.type == 'select'" 
                            :id="field.name"
                            :name="field.name"
                            :type="field.type"
                            :content="field.content"
                            class="mt-1 block w-full"
                            v-model="form[field.name as never]"
                            :multiple="field.multiple"
                        />
        
                        <InputError class="mt-2" :message="form.errors[field.name as never]" />
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
