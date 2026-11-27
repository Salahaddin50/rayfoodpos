export default [
    {
        path: "/admin/customers",
        component: () => import("../../components/admin/customers/CustomerComponent"),
        name: "admin.customers",
        redirect: {name: "admin.customers.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "customers",
            breadcrumb: "customers",
        },
        children: [
            {
                path: "",
                component: () => import("../../components/admin/customers/CustomerListComponent"),
                name: "admin.customers.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "customers",
                    breadcrumb: "",
                }
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/customers/CustomerShowComponent"),
                name: "admin.customers.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "customers",
                    breadcrumb: "view",
                }
            },
            {
                path: "show/:id/:orderId",
                component: () => import("../../components/admin/customers/CustomerOrderDetailsComponent"),
                name: "admin.customers.order.details",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "customers",
                    breadcrumb: "order_details",
                }
            },
        ],
    },
];
