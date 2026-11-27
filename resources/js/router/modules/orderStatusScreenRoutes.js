export default [
    {
        path: "/admin/order-status-screen",
        component: () => import("../../components/admin/orderStatusScreen/OrderStatusScreenComponent"),
        name: "admin.order-status-screen",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "order-status-screen",
        },
    },
];
