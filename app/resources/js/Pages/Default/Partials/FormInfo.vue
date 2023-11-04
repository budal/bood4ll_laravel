<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Button from '@/Components/Button.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { onBeforeMount, reactive } from 'vue';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import { trans } from 'laravel-vue-i18n';
import Select from '@/Components/Select.vue';

const passwordInput = ref<HTMLInputElement | null>(null);

const props = defineProps<{
    form: any;
    routes: any;
    data?: any;
}>();    

const url = window.location.href;

const jsForm = useForm({});

let items = reactive(new Set())

props.form.forEach((forms: any) => {
    forms.fields.forEach((fields: any) => {
        fields.forEach((field: any) => {
            items.add(field.name);
        });
    });
});

function dynamicFields() {
    let content = reactive(new Set())

    items.forEach((field: any) => {
        content[field as never] = jsForm[field as never] ;
    });

    return content;
}

onBeforeMount(() => {
    items.forEach((field: any) => {
        jsForm[field] = props.data ? props.data[field] : '';
    });
})

const sendForm = (formId: string) => {
    jsForm.transform(() => ({ ...dynamicFields() }))

    jsForm.submit(props.routes[formId].method, props.routes[formId].route, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(trans(usePage().props.status as string));
            jsForm.reset();
        },
        onError: () => {
                // if (jsForm.errors.password) {
                //     jsForm.reset('password', 'password_confirmation');
                //     passwordInput.value?.focus();
                // }
                // if (jsForm.errors.current_password) {
                //     jsForm.reset('current_password');
                //     currentPasswordInput.value?.focus();
                // }
            },
        onFinish: () => {
            // jsForm.reset('password', 'password_confirmation');
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
    <section v-for="mkForm in form">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $t(mkForm.title) }}</h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $t(mkForm.subtitle) }}
            </p>
        </header>
        
        <form @submit.prevent="sendForm(mkForm.id)" class="space-y-6 mt-2">
            <div class="flex flex-col">
                <div v-for="group in mkForm.fields" :class="`grid sm:grid-cols-${mkForm.cols} sm:gap-4`">
                    <div v-for="field in group" :class="`${field.span ? `sm:col-span-${field.span}` : ''} mt-4`">
                        <InputLabel as="span" :for="field.name" :value="$t(field.title) + (field.required ? ' *' : '')" />
        
                        <TextInput v-if="field.type == 'input'"
                            :id="field.name"
                            :name="field.name"
                            :type="field.type"
                            class="mt-1 block w-full"
                            v-model="jsForm[field.name as never]"
                            :required="field.required"
                            :autocomplete="field.name"
                        />

                        <Select v-if="field.type == 'select'" 
                            :id="field.name"
                            :name="field.name"
                            :type="field.type"
                            :content="field.content"
                            class="mt-1 block w-full"
                            v-model="jsForm[field.name as never]"
                            :required="field.required"
                            :multiple="field.multiple"
                        />
        
                        <InputError class="mt-2" :message="jsForm.errors[field.name as never]" />
                    </div>
                </div>
            </div>
    
            <div class="flex items-center gap-4">
                <Button 
                    color="primary" 
                    :class="{ 'opacity-25': jsForm.processing }"
                    :disabled="jsForm.processing">{{ $t('Save') }}
                </Button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="jsForm.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">{{ $t('Saved.') }}</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
