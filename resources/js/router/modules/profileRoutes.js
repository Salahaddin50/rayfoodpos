export default [
    {
        path: "/admin/profile/edit-profile",
        component: () => import("../../components/admin/profile/ProfileEditProfileComponent"),
        name: "admin.profile.editProfile",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "",
            breadcrumb: "edit_profile",
        },
    },
    {
        path: "/admin/profile/change-password",
        component: () => import("../../components/admin/profile/ProfileChangePasswordComponent"),
        name: "admin.profile.changePassword",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "",
            breadcrumb: "change_password",
        },
    }
];
