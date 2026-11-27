export default [
    {
        path: "/admin/drivers",
        component: () => import("../../components/admin/drivers/DriverComponent"),
        name: "admin.drivers",
        redirect: { name: "admin.drivers.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "drivers",
            breadcrumb: "drivers",
        },
        children: [
            {
                path: "",
                component: () => import("../../components/admin/drivers/DriverListComponent"),
                name: "admin.drivers.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "drivers",
                    breadcrumb: "",
                },
            },
        ],
    },
];


