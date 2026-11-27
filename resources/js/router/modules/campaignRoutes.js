export default [
    {
        path: '/admin/campaigns',
        component: () => import("../../components/admin/campaigns/CampaignComponent"),
        name: 'admin.campaigns',
        redirect: {name: 'admin.campaigns.list'},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'campaigns',
            breadcrumb: 'campaigns'
        },
        children: [
            {
                path: '',
                component: () => import("../../components/admin/campaigns/CampaignListComponent"),
                name: 'admin.campaigns.list',
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: 'campaigns',
                    breadcrumb: ''
                },
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/campaigns/CampaignShowComponent"),
                name: "admin.campaign.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "campaigns",
                    breadcrumb: "view",
                },
            },
        ]
    }
]
