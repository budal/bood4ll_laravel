import "./bootstrap";
import "../css/app.css";

import { createApp, h, DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import { i18nVue } from "laravel-vue-i18n";

import PrimeVue from "primevue/config";
import FocusTrap from "primevue/focustrap";
import Ripple from "primevue/ripple";
import StyleClass from "primevue/styleclass";
import ToastService from "primevue/toastservice";
import "primevue/resources/themes/aura-light-blue/theme.css";
// import "primevue/resources/themes/aura-dark-blue/theme.css";
import "primeicons/primeicons.css";
import InputIcon from "primevue/inputicon";
import IconField from "primevue/iconfield";
import Toast from "primevue/toast";

import { Icon } from "@iconify/vue";

import Vue3Toasity, { type ToastContainerOptions } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
// @ts-expect-error
import { modal } from "/vendor/emargareten/inertia-modal";

const appName = import.meta.env.VITE_APP_NAME || "Bood4ll";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    progress: { color: "#4B5563" },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(modal, {
            resolve: (name: string) =>
                resolvePageComponent(
                    `./Pages/${name}.vue`,
                    import.meta.glob("./Pages/**/*.vue"),
                ),
        })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(i18nVue, {
                lang: "pt_BR",
                resolve: (lang: string) => {
                    const langs = import.meta.glob("../../lang/*.json", {
                        eager: true,
                    });
                    return (langs[`../../lang/${lang}.json`] as any).default;
                },
            })
            .use(PrimeVue, { ripple: true })
            .directive("focustrap", FocusTrap)
            .directive("ripple", Ripple)
            .directive("styleclass", StyleClass)
            .component("InputIcon", InputIcon)
            .component("IconField", IconField)
            .component("Toast", Toast)
            .component("Icon", Icon)
            .use(ToastService)
            .use(Vue3Toasity, {
                autoClose: 3000,
                limit: 5,
                theme: "auto",
                style: {
                    opacity: "0.9",
                    userSelect: "initial",
                },
                toastStyle: {
                    fontSize: "14px",
                    fontFamily: "Figtree",
                },
            } as ToastContainerOptions)
            .mount(el);
    },
});
