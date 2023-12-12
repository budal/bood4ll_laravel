<script setup lang="ts">
import { toast } from "@/helpers";
import Button from "@/Components/Button.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Select from "@/Components/Select.vue";
import Table from "@/Components/Table/Index.vue";
import TextInput from "@/Components/TextInput.vue";
import Tabs from "@/Components/Tabs.vue";
import Toggle from "@/Components/Toggle.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = withDefaults(
    defineProps<{
        form: any;
        routes: any;
        data?: any;
        shortcutKey?: string;
        tabs?: boolean;
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
            // jsForm.reset('password', 'password_confirmation');
        },
    });
};

let tab = ref("");

const changeTab = (item: any) => {
    tab.value = item || null;
};
</script>

<template>
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
                                v-if="field.title"
                                as="span"
                                :for="field.name"
                                :value="$t(field.title)"
                                :required="field.required"
                            />

                            <TextInput
                                v-if="
                                    field.type == 'input' ||
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
                    class="flex items-center gap-4"
                >
                    <Button
                        color="primary"
                        :class="{ 'opacity-25': jsForm.processing }"
                        :disabled="
                            data?.inalterable === true || jsForm.processing
                        "
                    >
                        {{ $t("Save") }}
                    </Button>
                </div>
            </form>
        </template>
    </Tabs>
</template>
