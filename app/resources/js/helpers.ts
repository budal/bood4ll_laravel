import { usePage } from "@inertiajs/vue3";
import { transChoice } from "laravel-vue-i18n";
import { ReplacementsInterface } from "laravel-vue-i18n/interfaces/replacements";
import { ToastType, toast as toasty } from "vue3-toastify";

const isValidUrl = (url: string | { route: string; attributes: string[] }) => {
    if (url) {
        try {
            if (Boolean(new URL(url as string | URL))) return url;
        } catch (e) {
            const link =
                typeof url === "string" ? { route: url, attributes: [] } : url;

            if (route().has(link.route))
                return route(link.route, link.attributes);
            else console.log(`Error: route '${link.route}' doesn't exist.`);
        }
    }
};

function mkAttr(sourceAttributes: any, formValue: any) {
    const result: any = {};
    for (const key in sourceAttributes) {
        if (
            Object.prototype.hasOwnProperty.call(sourceAttributes, key) &&
            formValue.hasOwnProperty(sourceAttributes[key])
        ) {
            result[key] = formValue[sourceAttributes[key]];
        }
    }
    return result;
}

async function getData(route: any) {
    try {
        const response = await fetch(isValidUrl(route) as string);

        try {
            return await response.json();
        } catch (error) {
            console.error(`Error on load '${route}'.`);
        }
    } catch (error) {
        console.error(error);
    }
}

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

export { isValidUrl, mkAttr, getData, formatRouteWithID, toast };
