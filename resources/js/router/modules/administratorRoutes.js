export default [
    {
        path: "/admin/administrators",
        component: () => import("../../components/admin/administrators/AdministratorComponent"),
        name: "admin.administrators",
        redirect: {name: "admin.administrators.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "administrators",
            breadcrumb: "administrators",
        },
        children: [
            {
                path: "",
                component: () => import("../../components/admin/administrators/AdministratorListComponent"),
                name: "admin.administrators.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "administrators",
                    breadcrumb: "",
                }
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/administrators/AdministratorShowComponent"),
                name: "admin.administrators.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "administrators",
                    breadcrumb: "view",
                }
            },
            {
                path: "show/:id/:orderId",
                component: () => import("../../components/admin/administrators/AdministratorOrderDetailsComponent"),
                name: "admin.administrators.order.details",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "administrators",
                    breadcrumb: "order_details",
                },
            },
        ],
    },
];
