import CampaignComponent from "../../components/admin/campaigns/CampaignComponent";
import CampaignListComponent from "../../components/admin/campaigns/CampaignListComponent";
import CampaignShowComponent from "../../components/admin/campaigns/CampaignShowComponent";

export default [
    {
        path: '/admin/campaigns',
        component: CampaignComponent,
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
                component: CampaignListComponent,
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
                component: CampaignShowComponent,
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
