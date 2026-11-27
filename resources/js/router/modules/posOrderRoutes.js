export default [
    {
        path: "/admin/pos-orders",
        component: () => import("../../components/admin/posOrders/PosOrderComponent"),
        name: "admin.pos.orders",
        redirect: { name: "admin.pos.orders.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'pos-orders',
            breadcrumb: 'pos_orders'
        },
        children: [
            {
                path: "",
                component: () => import("../../components/admin/posOrders/PosOrderListComponent"),
                name: "admin.pos.orders.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos-orders",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/posOrders/PosOrderShowComponent"),
                name: "admin.pos.orders.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos-orders",
                    breadcrumb: "view",
                },
            }
        ],
    },
];
