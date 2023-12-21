import { usePage } from "@inertiajs/vue3";
import { transChoice } from "laravel-vue-i18n";
import { ReplacementsInterface } from "laravel-vue-i18n/interfaces/replacements";
import { ToastType, toast as toasty } from "vue3-toastify";

const isValidUrl = (url: any) => {
    try {
        if (Boolean(new URL(url))) return url;
    } catch (e) {
        const link =
            typeof url === "string" ? { route: url, attributes: [] } : url;
        return route(link.route, link.attributes);
    }
};

const formatRouteWithID = (url: any, id: any) =>
    isValidUrl(
        typeof url == "string"
            ? route(url, id)
            : {
                  route: url.route,
                  attributes: url.attributes ? [id, ...url.attributes] : [id],
              },
    );

const toast = () => {
    if (usePage().props.toast_message) {
        toasty(
            transChoice(
                usePage().props.toast_message as string,
                (usePage().props.toast_count || 0) as number,
                (usePage().props.toast_replacements ||
                    []) as ReplacementsInterface,
            ),
            {
                type: (usePage().props.toast_type || "info") as ToastType,
            },
        );
    }
};

export { isValidUrl, formatRouteWithID, toast };
