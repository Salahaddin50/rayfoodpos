export default [
    {
        path: "/admin/employees",
        component: () => import("../../components/admin/employees/EmployeeComponent"),
        name: "admin.employees",
        redirect: {name: "admin.employees.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "employees",
            breadcrumb: "employees",
        },
        children: [
            {
                path: "",
                component: () => import("../../components/admin/employees/EmployeeListComponent"),
                name: "admin.employees.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "employees",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/employees/EmployeeShowComponent"),
                name: "admin.employees.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "employees",
                    breadcrumb: "view",
                },
            },
            {
                path: "show/:id/:orderId",
                component: () => import("../../components/admin/employees/EmployeeOrderDetailsComponent"),
                name: "admin.employees.order.details",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "employees",
                    breadcrumb: "order_details",
                },
            },
        ],
    },
];
