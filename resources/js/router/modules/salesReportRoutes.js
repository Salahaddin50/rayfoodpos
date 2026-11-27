export default [
    {
        path: "/admin/sales-report",
        component: () => import("../../components/admin/salesReport/SalesReportComponent"),
        name: "admin.sales-report",
        redirect: { name: "admin.sales-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "sales-report",
            breadcrumb: "sales_report",
        },
        children: [
            {
                path: "",
                component: () => import("../../components/admin/salesReport/SalesReportListComponent"),
                name: "admin.sales-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "sales-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
