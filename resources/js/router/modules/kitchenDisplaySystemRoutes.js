export default [
    {
        path: "/admin/kitchen-display-system",
        component: () => import("../../components/admin/kitchenDisplaySystem/KitchenDisplaySystemComponent"),
        name: "admin.kitchen-display-system",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "kitchen-display-system",
        },
    },
];
