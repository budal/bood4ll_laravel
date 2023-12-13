<script setup lang="ts">
import { isValidUrl, toast } from "@/helpers";
import Button from "@/Components/Button.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Select from "@/Components/Select.vue";
import Table from "@/Components/Table/Index.vue";
import TextInput from "@/Components/TextInput.vue";
import Tabs from "@/Components/Tabs.vue";
import Toggle from "@/Components/Toggle.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = withDefaults(
    defineProps<{
        form: any;
        routes: any;
        data?: any;
        shortcutKey?: string;
        tabs?: boolean;
        status?: string | null;
    }>(),
    {
        tabs: true,
    },
);

interface FormItems {
    [key: string]: string;
}

let formItems: FormItems = {};

props.form.forEach((forms: any) => {
    forms.fields.forEach((fields: any) => {
        fields.forEach((field: any) => {
            formItems[field.name] = props.data
                ? props?.data[field.name] || ""
                : "";
        });
    });
});

const jsForm = useForm(formItems);

const sendForm = (formId: string) => {
    jsForm.submit(props.routes[formId].method, props.routes[formId].route, {
        preserveScroll: true,
        onSuccess: () => {
            toast();
        },
        onError: () => {
            toast();

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
            props.routes[formId].reset === true
                ? props.routes[formId].fieldsToReset
                    ? jsForm.reset(...props.routes[formId].fieldsToReset)
                    : jsForm.reset()
                : false;
        },
    });
};

let tab = ref("");

const changeTab = (item: any) => {
    tab.value = item || null;
};
</script>

<template>
    <div
        v-if="status"
        class="mb-4 rounded-md font-medium bg-success-light dark:bg-success-dark text-sm text-center text-success-light dark:text-success-dark"
    >
        {{ status }}
    </div>

    <Tabs :items="form" :tabs="tabs" @change-tab="changeTab">
        <template v-for="mkForm in form" v-slot:[mkForm.id]>
            <form
                v-if="mkForm.condition !== false"
                @submit.prevent="sendForm(mkForm.id)"
                class="space-y-6"
            >
                <div
                    v-for="group in mkForm.fields"
                    :class="`grid sm:grid-cols-${mkForm.cols} sm:gap-4`"
                >
                    <template v-for="field in group">
                        <div
                            :class="`${
                                field.span ? `sm:col-span-${field.span}` : ''
                            }`"
                        >
                            <InputLabel
                                v-if="field.title && field.type != 'checkbox'"
                                as="span"
                                :for="field.name"
                                :value="$t(field.title)"
                                :required="field.required"
                            />

                            <label
                                v-if="field.type == 'checkbox'"
                                class="flex items-center"
                            >
                                <Checkbox
                                    name="remember"
                                    :checked="
                                        jsForm[field.name] == 'true'
                                            ? true
                                            : false
                                    "
                                />
                                <span
                                    class="ml-2 text-sm text-zero-light dark:text-zero-dark"
                                    >{{ $t("Remember me") }}</span
                                >
                            </label>

                            <div
                                v-if="field.type == 'links'"
                                class="flex justify-between underline text-sm text-zero-light dark:text-zero-dark"
                            >
                                <template v-for="link in field.values">
                                    <Link
                                        v-if="link.condition === true"
                                        :href="isValidUrl(link.route)"
                                        class="focus:outline-none border-b-2 border-transparent hover:border-zero-dark dark:hover:border-zero-white focus:border-zero-dark dark:focus:border-zero-white transition ease-in-out duration-500"
                                    >
                                        {{ $t(link.title) }}
                                    </Link>
                                </template>
                            </div>

                            <TextInput
                                v-if="
                                    field.type == 'input' ||
                                    field.type == 'password' ||
                                    field.type == 'date' ||
                                    field.type == 'email'
                                "
                                :id="field.name"
                                :name="field.name"
                                :type="field.type"
                                :mask="field.mask"
                                class="mt-1"
                                v-model="jsForm[field.name]"
                                :disabled="
                                    field.disabled
                                        ? field.disabled
                                        : data?.inalterable === true
                                "
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
                                :rotate="field.rotate"
                                :disabled="
                                    field.disabled
                                        ? field.disabled
                                        : data?.inalterable === true
                                "
                                class="mt-1"
                                v-model="jsForm[field.name]"
                                @keydown.enter.prevent
                                @click.prevent
                            />

                            <Button
                                v-if="field.type == 'button'"
                                :id="field.name"
                                :name="field.name"
                                :type="field.type"
                                :color="field.color"
                                :link="field.route"
                                :title="field.name"
                                :startIcon="field.icon"
                                :preserveScroll="field.preserveScroll"
                                :disabled="
                                    field.disabled
                                        ? field.disabled
                                        : data?.inalterable === true
                                "
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
                                :class="{
                                    'opacity-25': data?.inalterable === true,
                                }"
                                v-model="jsForm[field.name]"
                                :disabled="
                                    field.disabled
                                        ? field.disabled
                                        : data?.inalterable === true
                                "
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
                                :tab="tab"
                                :shortcutKey="field.shortcutKey"
                                :menu="field.content.menu"
                                :routes="field.content.routes"
                                :items="field.content.items"
                                :titles="field.content.titles"
                            />

                            <InputError
                                class="mt-2"
                                :message="jsForm.errors[field.name]"
                            />
                        </div>
                    </template>
                </div>

                <div
                    v-if="props.routes[mkForm.id]"
                    class="flex gap-4"
                    :class="
                        props.routes[mkForm.id].buttonClass || 'justify-start'
                    "
                >
                    <Button
                        color="primary"
                        :class="{ 'opacity-25': jsForm.processing }"
                        :disabled="
                            data?.inalterable === true || jsForm.processing
                        "
                    >
                        {{ $t(props.routes[mkForm.id].buttonTitle || "Save") }}
                    </Button>
                </div>
            </form>
        </template>
    </Tabs>
</template>
