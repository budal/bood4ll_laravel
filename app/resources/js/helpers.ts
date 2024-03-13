import { usePage } from "@inertiajs/vue3";
import axios, { AxiosHeaderValue } from "axios";
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
    route: any,
    options?: {
        method?: "get" | "post" | "put" | "patch" | "delete";
        data?: BodyInit | null | undefined;
        onCancel?: Function;
        onBefore?: Function;
        onProgress?: Function;
        onSuccess?: Function;
        onError?: Function;
        onFinish?: Function;
    },
) {
    options = options || { method: "get", data: null };

    try {
        const response = await fetch(isValidUrl(route) as string, {
            method: options.method,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf as string,
            },
            body: options.data,
        });

        console.log(response);

        if (response) {
        }

        try {
            return await response.json();
        } catch (error) {
            console.error(`Error on load '${route}'.`);
        }
    } catch (error) {
        console.error(error);
    }
}

async function fetchData2(
    route: any,
    options?: {
        method?: "get" | "post" | "put" | "patch" | "delete";
        data?: BodyInit | null | undefined;
        onBefore?: Function;
        onProgress?: Function;
        onCancel?: Function;
        onSuccess?: Function;
        onError?: Function;
        onFinish?: Function;
    },
) {
    options = options || { method: "get", data: null };

    try {
        setTimeout(() => (options?.onBefore ? options.onBefore() : null));

        const instance = axios;

        // instance.defaults.headers.common["X-CSRF-TOKEN"] = usePage().props
        //     .csrf as AxiosHeaderValue;

        await instance({
            url: isValidUrl(route),
            method: options?.method,
            // headers: {
            //     Accept: "application/json",
            //     "Content-Type": "application/json;charset=UTF-8",
            //     "X-CSRF-TOKEN": usePage().props.csrf as string,
            // },
            timeout: 10000,
            // onUploadProgress: function (progressEvent) {
            //     var percentCompleted = Math.round(
            //         (progressEvent.loaded * 100) / progressEvent.total,
            //     );
            //     console.log(progressEvent);
            // },
            // onDownloadProgress: function (progressEvent) {
            //     var percentCompleted = Math.round(
            //         (progressEvent.loaded * 100) / progressEvent.total,
            //     );
            //     console.log(progressEvent);
            // },
        })
            .then((response) => {
                if (options?.onSuccess) options.onSuccess(response.data);
            })
            .catch((error) => {
                if (error.code === "ECONNABORTED") {
                    setTimeout(() =>
                        options?.onCancel ? options.onCancel() : null,
                    );
                }
                if (options?.onError) options.onError(error);
            });

        setTimeout(() => (options?.onFinish ? options.onFinish() : null));
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

export {
    isValidUrl,
    mkAttr,
    mkRoute,
    fetchData,
    fetchData2,
    formatRouteWithID,
    toast,
};
