export default [
    {
        path: "/admin/takeaway-types",
        component: () => import("../../components/admin/takeawayType/TakeawayTypeComponent"),
        name: "admin.takeawayType",
        redirect: { name: "admin.takeawayType.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "takeaway-types",
            breadcrumb: "takeaway_types",
        },
        children: [
            {
                path: "list",
                component: () => import("../../components/admin/takeawayType/TakeawayTypeListComponent"),
                name: "admin.takeawayType.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "takeaway-types",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/takeawayType/TakeawayTypeShowComponent"),
                name: "admin.takeawayType.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "takeaway-types",
                    breadcrumb: "view",
                },
            },
        ],
    },
]



