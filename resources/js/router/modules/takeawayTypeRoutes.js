import TakeawayTypeListComponent from "../../components/admin/takeawayType/TakeawayTypeListComponent";
import TakeawayTypeComponent from "../../components/admin/takeawayType/TakeawayTypeComponent";
import TakeawayTypeShowComponent from "../../components/admin/takeawayType/TakeawayTypeShowComponent";

export default [
    {
        path: "/admin/takeaway-types",
        component: TakeawayTypeComponent,
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
                component: TakeawayTypeListComponent,
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
                component: TakeawayTypeShowComponent,
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



