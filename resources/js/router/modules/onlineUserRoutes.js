import OnlineUserComponent from "../../components/admin/onlineUsers/OnlineUserComponent";
import OnlineUserListComponent from "../../components/admin/onlineUsers/OnlineUserListComponent";

export default [
    {
        path: "/admin/online-users",
        component: OnlineUserComponent,
        name: "admin.onlineUsers",
        redirect: { name: "admin.onlineUsers.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "online_users",
            breadcrumb: "online_users",
        },
        children: [
            {
                path: "",
                component: OnlineUserListComponent,
                name: "admin.onlineUsers.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "online_users",
                    breadcrumb: "",
                },
            },
        ],
    },
];


