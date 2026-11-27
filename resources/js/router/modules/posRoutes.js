export default [
    {
        path: "/admin/pos",
        component: () => import("../../components/admin/pos/PosComponent"),
        name: "admin.pos",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "pos",
        },
    },
];
