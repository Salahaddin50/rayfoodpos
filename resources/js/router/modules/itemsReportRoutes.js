export default [
    {
        path: "/admin/items-report",
        component: () => import("../../components/admin/itemsReport/ItemsReportComponent"),
        name: "admin.items-report",
        redirect: { name: "admin.items-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "items-report",
            breadcrumb: "items_report",
        },
        children: [
            {
                path: "",
                component: () => import("../../components/admin/itemsReport/ItemsReportListComponent"),
                name: "admin.items-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "items-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
