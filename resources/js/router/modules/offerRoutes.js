export default [
    {
        path: '/admin/offers',
        component: () => import("../../components/admin/offers/OfferComponent"),
        name: 'admin.offers',
        redirect: {name: 'admin.offers.list'},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'offers',
            breadcrumb: 'offers'
        },
        children: [
            {
                path: '',
                component: () => import("../../components/admin/offers/OfferListComponent"),
                name: 'admin.offers.list',
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: 'offers',
                    breadcrumb: ''
                },
            },
            {
                path: "show/:id",
                component: () => import("../../components/admin/offers/OfferShowComponent"),
                name: "admin.offer.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "offers",
                    breadcrumb: "view",
                },
            },
        ]
    }
]
