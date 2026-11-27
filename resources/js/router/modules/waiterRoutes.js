export default [
    {
        path: "/admin/waiters",
        component: () => import("../../components/admin/waiters/WaiterComponent.vue"),
        name: "admin.waiters",
        redirect: {name: "admin.waiters.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "waiters",
            breadcrumb: "waiters",
        },
        children: [
            {
                path: "",
                component: () => import("../../components/admin/waiters/WaiterListComponent.vue"),
                name: "admin.waiters.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "waiters",
                    breadcrumb: "",
                }
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/waiters/WaiterShowComponent.vue"),
                name: "admin.waiters.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "waiters",
                    breadcrumb: "view",
                }
            },
            {
                path: "show/:id/:orderId",
                component: () => import("../../components/admin/waiters/WaiterOrderDetailsComponent.vue"),
                name: "admin.waiters.order.details",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "waiters",
                    breadcrumb: "order_details",
                }
            },
        ],
    },
];
