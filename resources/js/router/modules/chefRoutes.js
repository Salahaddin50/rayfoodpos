export default [
    {
        path: "/admin/chefs",
        component: () => import("../../components/admin/chefs/ChefComponent.vue"),
        name: "admin.chefs",
        redirect: { name: "admin.chefs.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "chefs",
            breadcrumb: "chefs",
        },
        children: [
            {
                path: "",
                component: () => import("../../components/admin/chefs/ChefListComponent.vue"),
                name: "admin.chefs.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "chefs",
                    breadcrumb: "",
                }
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/chefs/ChefShowComponent.vue"),
                name: "admin.chefs.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "chefs",
                    breadcrumb: "view",
                }
            },
            {
                path: "show/:id/:orderId",
                component: () => import("../../components/admin/chefs/ChefOrderDetailsComponent.vue"),
                name: "admin.chefs.order.details",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "chefs",
                    breadcrumb: "order_details",
                }
            },
        ],
    },
];
