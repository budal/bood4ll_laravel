import { usePage } from "@inertiajs/vue3";
import { transChoice } from "laravel-vue-i18n";
import { ReplacementsInterface } from "laravel-vue-i18n/interfaces/replacements";
import { ref } from "vue";
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

function mkAttr(attributes: any, formValue: any) {
    const result: any = {};
    for (const key in attributes) {
        if (
            Object.prototype.hasOwnProperty.call(attributes, key) &&
            formValue.hasOwnProperty(attributes[key])
        ) {
            result[key] = formValue[attributes[key]];
        }
    }
    return result;
}

function mkRoute(component: any, id: any) {
    const mkAttributes = mkAttr(
        component.sourceAttributes,
        ref({ id: id }).value,
    );

    const attributes = Object.assign(
        {},
        component.source.attributes,
        mkAttributes,
    );

    const route =
        typeof component.source === "object"
            ? component.source.route
            : component.source;

    return { route: route, attributes: attributes };
}

async function fetchData(
    route?: any,
    options?: {
        method?: "get" | "post" | "put" | "patch" | "delete";
        data?: BodyInit | null | undefined;
    },
) {
    route = route || "";
    options = options || { method: "get", data: null };

    try {
        const response = await fetch(isValidUrl(route) as string, {
            method: options.method || "get",
            body: options.data || null,
        });
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

export { isValidUrl, mkAttr, mkRoute, fetchData, formatRouteWithID, toast };
