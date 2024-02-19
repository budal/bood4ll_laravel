import "./bootstrap";
import "../css/app.css";

import { createApp, h, DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import { i18nVue } from "laravel-vue-i18n";
import PrimeVue from "primevue/config";
import Vue3Toasity, { type ToastContainerOptions } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
// @ts-expect-error
import { modal } from "/vendor/emargareten/inertia-modal";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    progress: { color: "#4B5563" },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(modal, {
                resolve: (name: string) =>
                    resolvePageComponent(
                        `./Pages/${name}.vue`,
                        import.meta.glob("./Pages/**/*.vue"),
                    ),
            })
            .use(plugin)
            .use(PrimeVue, {
                unstyled: true,
            })
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
            .use(i18nVue, {
                lang: "pt_BR",
                resolve: (lang: string) => {
                    const langs = import.meta.glob("../../lang/*.json", {
                        eager: true,
                    });
                    return (langs[`../../lang/${lang}.json`] as any).default;
                },
            })
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
});
