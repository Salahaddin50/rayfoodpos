export default [
    {
        path: "/admin/dining-tables",
        component: () => import("../../components/admin/diningTable/DiningTableComponent"),
        name: "admin.diningTable",
        redirect: { name: "admin.diningTable.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "dining-tables",
            breadcrumb: "dining_tables",
        },
        children: [
            {
                path: "list",
                component: () => import("../../components/admin/diningTable/DiningTableListComponent"),
                name: "admin.diningTable.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "dining-tables",
                    breadcrumb: "",
                },
            },
            {
                path: "overview",
                component: () => import("../../components/admin/diningTable/DiningTableOverviewComponent"),
                name: "admin.diningTable.overview",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "dining-tables",
                    breadcrumb: "table_overview",
                },
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/diningTable/DiningTableShowComponent"),
                name: "admin.diningTable.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "dining-tables",
                    breadcrumb: "view",
                },
            },
        ],
    },
]
