export default [
    {
        path: "/admin/online-users",
        component: () => import("../../components/admin/onlineUsers/OnlineUserComponent"),
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
                component: () => import("../../components/admin/onlineUsers/OnlineUserListComponent"),
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


