import { router } from "@inertiajs/vue3";
import axios from "axios";

const isValidUrl = (url: string | { route: string; attributes?: string[] }) => {
    if (url) {
        try {
            if (Boolean(new URL(url as string | URL))) return url;
        } catch (e) {
            const link =
                typeof url === "string" ? { route: url, attributes: [] } : url;

            const attributes = link.attributes;

            if (route().has(link.route)) return route(link.route, attributes);
            else console.log(`Error: route '${link.route}' doesn't exist.`);
        }
    }
};

async function fetchData(
    route: any,
    options?: {
        complement?: any;
        method?: "get" | "post" | "put" | "patch" | "delete";
        data?: Object;
        onBefore?: Function;
        onProgress?: Function;
        onCancel?: Function;
        onSuccess?: Function;
        onError?: Function;
        onFinish?: Function;
    },
) {
    let routeUrl = route;

    if (options?.complement) {
        routeUrl =
            typeof route === "string"
                ? { route: route, attributes: options.complement }
                : {
                      route: route.route,
                      attributes: Object.assign(
                          {},
                          route.attributes,
                          options.complement,
                      ),
                  };

        if (route.transmute) {
            const transmuteAttributes: any = {};

            for (const key in route.transmute) {
                if (
                    Object.prototype.hasOwnProperty.call(
                        route.transmute,
                        key,
                    ) &&
                    options.complement.hasOwnProperty(route.transmute[key])
                ) {
                    transmuteAttributes[key] =
                        options.complement[route.transmute[key]];
                }
            }

            routeUrl = {
                route: routeUrl.route,
                attributes: Object.assign(
                    {},
                    routeUrl.attributes,
                    transmuteAttributes,
                ),
            };
        }
    }

    try {
        setTimeout(() => (options?.onBefore ? options.onBefore() : null));

        const instance = axios;

        // console.log(isValidUrl(routeUrl));

        await instance({
            url: isValidUrl(routeUrl) as string,
            method: options?.method,
            data: options?.data,
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
                if (response.data.redirect === true) {
                    router.visit(response.data.url);
                } else {
                    if (options?.onSuccess) {
                        options.onSuccess(response.data);
                    }
                }
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

export { isValidUrl, fetchData, formatRouteWithID };
