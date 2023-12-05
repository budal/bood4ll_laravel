<script setup lang="ts">
    import Button from '@/Components/Button.vue';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import Select from '@/Components/Select.vue';
    import Table from '@/Components/Table/Index.vue';
    import TextInput from '@/Components/TextInput.vue';
    import Toggle from '@/Components/Toggle.vue';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { toast } from 'vue3-toastify';
    import { trans } from 'laravel-vue-i18n';

    const props = defineProps<{
        form: any;
        routes: any;
        data?: any;
        shortcutKey?: string;
    }>();    

    interface FormItems {
        [key: string]: string;
    }

    let formItems: FormItems = {};

    props.form.forEach((forms: any) => {
        forms.fields.forEach((fields: any) => {
            fields.forEach((field: any) => {
                formItems[field.name] = props.data ? props?.data[field.name] || '' : '';
            });
        });
    });

    const jsForm = useForm(formItems);
    
    const sendForm = (formId: string) => {
        jsForm.submit(props.routes[formId].method, props.routes[formId].route, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(trans(usePage().props.status as string));
            },
            onError: () => {
                toast.error('123');

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
</script>

<template>
    <div class="grid grid-cols-1 gap-10">
        <template v-for="mkForm in form">
            <section 
                v-if="mkForm.condition !== false" 
                class="p-2 bg-zero-light dark:bg-zero-dark rounded-xl border-2 border-zero-light dark:border-zero-dark shadow-primary-light/20 dark:shadow-primary-dark/20 shadow-[0_2px_10px]"
            >
                <form @submit.prevent="sendForm(mkForm.id)" class="space-y-6">
                    <div class="flex flex-col">
                        <template v-for="group in mkForm.fields">
                            <header v-if="mkForm.title || mkForm.subtitle" class="mb-2">
                                <h2 v-if="mkForm.title" class="text-lg font-medium text-zero-light dark:text-zero-dark">{{ $t(mkForm.title) }}</h2>
                                <p v-if="mkForm.subtitle" class="mt-1 text-sm text-zero-light/50 dark:text-zero-dark/50">{{ $t(mkForm.subtitle) }}</p>
                            </header>
                            <div :class="`grid sm:grid-cols-${mkForm.cols} sm:gap-4`">
                                <template v-for="field in group">
                                    <div :class="`${field.span ? `sm:col-span-${field.span}` : ''}`">
                                        <InputLabel 
                                            v-if="field.title"
                                            as="span" 
                                            :for="field.name" 
                                            :value="$t(field.title)" 
                                            :required="field.required" 
                                        />

                                        <TextInput 
                                            v-if="field.type == 'input' || field.type == 'date' || field.type == 'email'"
                                            :id="field.name"
                                            :name="field.name"
                                            :type="field.type"
                                            :mask="field.mask"
                                            class="mt-1"
                                            v-model="jsForm[field.name]"
                                            :disabled="field.disabled ? field.disabled : data?.inalterable === true"
                                            :required="field.required"
                                            :autocomplete="field.autocomplete"
                                            :autofocus="field.autofocus"
                                        />
                
                                        <Toggle 
                                            v-if="field.type == 'toggle'"
                                            :id="field.name"
                                            :name="field.name"
                                            :type="field.type"
                                            :colorOn="field.colorOn"
                                            :colorOff="field.colorOff"
                                            :disabled="field.disabled ? field.disabled : data?.inalterable === true"
                                            class="mt-1"
                                            v-model="jsForm[field.name]"
                                            @keydown.enter.prevent
                                            @click.prevent
                                        />
                
                                        <Select 
                                            v-if="field.type == 'select'" 
                                            :id="field.name"
                                            :name="field.name"
                                            :content="field.content"
                                            class="mt-1 block w-full"
                                            :class="{ 'opacity-25': data?.inalterable === true }"
                                            v-model="jsForm[field.name]"
                                            :disabled="field.disabled ? field.disabled : data?.inalterable === true"
                                            :required="field.required"
                                            :autocomplete="field.autocomplete"
                                            :multiple="field.multiple"
                                            @keydown.enter.prevent
                                        />
                
                                        <Table 
                                            v-if="field.type == 'table'"
                                            :prefix="mkForm.id"
                                            :id="field.name"
                                            :name="field.name"
                                            :shortcutKey="field.shortcutKey"
                                            :menu="field.content.menu" 
                                            :routes="field.content.routes" 
                                            :items="field.content.items" 
                                            :titles="field.content.titles" 
                                        />
                        
                                        <InputError class="mt-2" :message="jsForm.errors[field.name]" />
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
            
                    <div v-if="props.routes[mkForm.id]" class="flex items-center gap-4">
                        <Button 
                            color="primary" 
                            :class="{ 'opacity-25': jsForm.processing }"
                            :disabled="data?.inalterable === true || jsForm.processing"
                        >
                            {{ $t('Save') }}
                        </Button>
        
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="jsForm.recentlySuccessful" class="text-sm text-zero-light/50 dark:text-zero-dark/50">{{ $t('Saved.') }}</p>
                        </Transition>
                    </div>
                </form>
            </section>
        </template>
    </div>
</template>
