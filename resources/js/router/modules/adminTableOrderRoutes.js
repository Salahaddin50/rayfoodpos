export default [
    {
        path: '/admin/table-orders',
        component: () => import("../../components/admin/tableOrders/TableOrderComponent"),
        name: 'admin.table.order',
        redirect: {name: 'admin.table.order.list'},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'table-orders',
            breadcrumb: 'table_orders'
        },
        children: [
            {
                path: '',
                component: () => import("../../components/admin/tableOrders/TableOrderListComponent"),
                name: 'admin.table.order.list',
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: 'table-orders',
                    breadcrumb: ''
                },
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/tableOrders/TableOrderShowComponent"),
                name: "admin.table.order.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "table-orders",
                    breadcrumb: "view",
                },
            }
        ]
    }
]
