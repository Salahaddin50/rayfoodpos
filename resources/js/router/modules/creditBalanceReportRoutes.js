export default [
    {
        path: "/admin/credit-balance-report",
        component: () => import("../../components/admin/creditBalanceReport/CreditBalanceReportComponent"),
        name: "admin.credit-balance-report",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "credit-balance-report",
            breadcrumb: "credit_balance_report",
        },
    },
];
