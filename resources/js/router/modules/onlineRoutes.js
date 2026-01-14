import OnlineComponent from "../../components/online/OnlineComponent";
import SearchItemComponent from "../../components/table/search/SearchItemComponent.vue";
import PageComponent from "../../components/table/page/PageComponent.vue";
import CheckoutComponent from "../../components/table/checkout/CheckoutComponent.vue";
import OrderDetailsComponent from "../../components/table/order/OrderDetailsComponent.vue";

export default [
    {
        path: "/online",
        component: OnlineComponent,
        name: "online",
        meta: {
            isTable: true,
            auth: false,
        },
    },
    {
        path: "/online/search",
        component: SearchItemComponent,
        name: "online.search",
        meta: {
            isTable: true,
            auth: false,
        },
    },
    {
        path: "/online/page/:pageSlug",
        component: PageComponent,
        name: "online.page",
        meta: {
            isTable: true,
            auth: false,
        },
    },
    {
        path: "/online/checkout",
        component: CheckoutComponent,
        name: "online.checkout",
        meta: {
            isTable: true,
            auth: false,
        },
    },
    {
        path: "/online/order/:id",
        component: OrderDetailsComponent,
        name: "online.order.details",
        meta: {
            isTable: true,
            auth: false,
        },
    },
];

