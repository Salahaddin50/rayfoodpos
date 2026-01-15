import OnlineBranchSelectionComponent from "../../components/online/OnlineBranchSelectionComponent.vue";
import OnlineMenuComponent from "../../components/online/OnlineMenuComponent.vue";
import OnlineSearchItemComponent from "../../components/online/OnlineSearchItemComponent.vue";
import OnlinePageComponent from "../../components/online/OnlinePageComponent.vue";
import OnlineCheckoutComponent from "../../components/online/OnlineCheckoutComponent.vue";
import OnlineOrderDetailsComponent from "../../components/online/OnlineOrderDetailsComponent.vue";

export default [
    {
        path: "/online",
        component: OnlineMenuComponent,
        name: "online.menu",
        meta: {
            isTable: true,
            auth: false,
        },
    },
    {
        path: "/online/menu/:branchId?",
        component: OnlineMenuComponent,
        name: "online.menu.branch",
        meta: {
            isTable: true,
            auth: false,
        },
    },
    {
        path: "/online/search/:branchId",
        component: OnlineSearchItemComponent,
        name: "online.search",
        meta: {
            isTable: true,
            auth: false,
        },
    },
    {
        path: "/online/page/:branchId/:pageSlug",
        component: OnlinePageComponent,
        name: "online.page",
        meta: {
            isTable: true,
            auth: false,
        },
    },
    {
        path: "/online/checkout/:branchId",
        component: OnlineCheckoutComponent,
        name: "online.checkout",
        meta: {
            isTable: true,
            auth: false,
        },
    },
    {
        path: "/online/order/:branchId/:id",
        component: OnlineOrderDetailsComponent,
        name: "online.order.details",
        meta: {
            isTable: true,
            auth: false,
        },
    },
];

