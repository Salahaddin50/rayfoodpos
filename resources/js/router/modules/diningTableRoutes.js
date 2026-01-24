import DiningTableListComponent from "../../components/admin/diningTable/DiningTableListComponent";
import DiningTableComponent from "../../components/admin/diningTable/DiningTableComponent";
import DiningTableShowComponent from "../../components/admin/diningTable/DiningTableShowComponent";
import DiningTableOverviewComponent from "../../components/admin/diningTable/DiningTableOverviewComponent";

export default [
    {
        path: "/admin/dining-tables",
        component: DiningTableComponent,
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
                component: DiningTableListComponent,
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
                component: DiningTableOverviewComponent,
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
                component: DiningTableShowComponent,
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
