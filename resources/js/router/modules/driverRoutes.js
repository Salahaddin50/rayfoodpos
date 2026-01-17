import DriverComponent from "../../components/admin/drivers/DriverComponent";
import DriverListComponent from "../../components/admin/drivers/DriverListComponent";

export default [
    {
        path: "/admin/drivers",
        component: DriverComponent,
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
                component: DriverListComponent,
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


