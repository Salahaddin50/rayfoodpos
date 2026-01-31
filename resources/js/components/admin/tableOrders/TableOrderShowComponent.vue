<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card p-4">
            <div class="flex flex-wrap gap-y-5 items-end justify-between">
                <div>
                    <div class="flex flex-wrap items-start gap-y-2 gap-x-6 mb-5">
                        <p class="text-2xl font-medium">
                            {{ $t("label.order_id") }}:
                            <span class="text-heading"> #{{ order.order_serial_no }} </span>
                        </p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span :class="'text-xs capitalize h-5 leading-5 px-2 rounded-3xl text-[#FB4E4E] bg-[#FFDADA]' +
                                statusClass(order.payment_status)
                                ">
                                {{ paymentStatusEnumArray[order.payment_status] }}
                            </span>
                            <span :class="'text-xs capitalize px-2 rounded-3xl ' + orderStatusClass(order.status)
                                ">
                                {{ orderStatusEnumArray[order.status] }}
                            </span>

                            <span v-if="order.is_advance_order === enums.isAdvanceOrderEnum.YES"
                                class="text-xs px-2 rounded-3xl advance-order py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap">
                                {{ $t("label.advance") }}
                            </span>
                        </div>
                    </div>
                    <ul class="flex flex-col gap-2">
                        <li class="flex items-center gap-2">
                            <i class="lab lab-calendar-line lab-font-size-16"></i>
                            <span class="text-xs">{{ order.order_datetime }}</span>
                        </li>
                        <li class="text-xs">
                            {{ $t("label.payment_type") }}:
                            <span class="text-heading" v-if="order.transaction">
                                {{ order.transaction.payment_method }}
                            </span>
                            <span v-else class="text-heading">
                                {{ paymentTypeEnumArray[order.payment_method] }}
                            </span>
                        </li>
                        <li class="text-xs">
                            {{ $t("label.order_type") }}:
                            <span class="text-heading" v-if="order.dining_table_id && order.table_name">
                                {{ orderTypeEnumArray[order.order_type] }}
                            </span>
                            <span class="text-heading" v-else>
                                {{ $t('label.online_order') }}
                            </span>
                        </li>
                        <li class="text-xs">
                            {{ $t("label.delivery_time") }}:
                            <span class="text-heading">
                                {{ order.delivery_date }} {{ order.delivery_time }}
                            </span>
                        </li>
                        <li class="text-xs" v-if="order.table_name">
                            {{ $t("label.table_name") }}:
                            <span class="text-heading">
                                {{ order.table_name }}
                            </span>
                        </li>
                        <li class="text-xs" v-if="order.token">
                            {{ $t("label.token_no") }}:
                            <span class="text-heading">
                                #{{ order.token }}
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="flex flex-wrap gap-3" v-if="order.status === enums.orderStatusEnum.PENDING">
                    <TableOrderReasonComponent />
                    <button type="button" @click="changeStatus(enums.orderStatusEnum.ACCEPT)"
                        class="flex items-center justify-center text-white gap-2 px-4 h-[38px] rounded shadow-db-card bg-[#2AC769]">
                        <i class="lab lab-save"></i>
                        <span class="text-sm capitalize text-white">{{ $t("button.accept") }}</span>
                    </button>
                </div>

                <div class="flex flex-wrap gap-3" v-else-if="order.status !== enums.orderStatusEnum.REJECTED &&
                    order.status !== enums.orderStatusEnum.CANCELED
                ">
                    <div class="relative" v-if="!order.token">
                        <TableOrderTokenComponent />
                    </div>

                    <div class="dropdown-group" v-if="order.transaction === null">
                        <button
                            class="min-w-[97px] flex items-center gap-4 justify-start text-sm capitalize appearance-none pl-2 h-[38px] rounded border border-primary bg-white text-primary dropdown-btn">
                            <span class="flex-1 text-start">{{ paymentStatusEnumArray[order.payment_status]
                            }}</span>
                            <i class="lab lab-arrow-down-2 lab-font-size-17 mx-1"></i>
                        </button>
                        <ul
                            class="p-2 rounded-lg shadow-xl absolute top-10 ltr:right-0 rtl:left-0 z-10 bg-white transition-all duration-300 origin-top scale-y-0 dropdown-list w-full">
                            <li class="active flex items-center gap-2 py-1 px-2.5 rounded-md cursor-pointer hover:bg-gray-100"
                                v-for="paymentStatus in paymentStatusObject" :key="paymentStatus.value"
                                @click="changePaymentStatus(paymentStatus.value)">
                                <span class="text-heading capitalize text-sm"
                                    :class="order.payment_status === paymentStatus.value ? 'text-primary' : ''">{{
                                        paymentStatus.name
                                    }}</span>

                            </li>
                        </ul>
                    </div>

                    <div class="dropdown-group">
                        <button
                            class="min-w-[150px] flex items-center justify-start text-sm capitalize appearance-none pl-2 h-[38px] rounded border border-primary bg-white text-primary dropdown-btn">
                            <span class="flex-1 text-start">{{ orderStatusEnumArray[order.status] }}</span>
                            <i class="lab lab-arrow-down-2 lab-font-size-17 mx-1"></i>
                        </button>
                        <ul
                            class="p-2 rounded-lg shadow-xl absolute top-10 ltr:right-0 rtl:left-0 z-10 bg-white transition-all duration-300 origin-top scale-y-0 dropdown-list w-full">
                            <li class="active flex items-center gap-2 py-1 px-2.5 rounded-md cursor-pointer hover:bg-gray-100"
                                v-for="status in orderStatusObject" :key="status.value"
                                @click="orderStatus(status.value)">
                                <span class="text-heading capitalize text-sm"
                                    :class="order.status === status.value ? 'text-primary' : ''">{{ status.name
                                    }}</span>

                            </li>
                        </ul>
                    </div>

                    <TableOrderReceiptComponent :order="order" :orderItems="orderItems" :orderUser="orderUser" />
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 sm:col-6">
        <div class="row">
            <div class="col-12">
                <div class="db-card">
                    <div class="db-card-header">
                        <h3 class="db-card-title">{{ $t("label.order_details") }}</h3>
                    </div>
                    <div class="db-card-body">
                        <div class="pl-3">
                            <div class="mb-3 pb-3 border-b last:mb-0 last:pb-0 last:border-b-0 border-gray-2"
                                v-if="orderItems.length > 0" v-for="item in orderItems" :key="item">
                                <div class="flex items-center gap-3 relative">
                                    <h3
                                        class="absolute top-5 -left-3 text-sm w-[26px] h-[26px] leading-[26px] text-center rounded-full text-white bg-heading">
                                        {{ item.quantity }}
                                    </h3>
                                    <img class="w-16 h-16 rounded-lg flex-shrink-0" :src="item.item_image"
                                        alt="thumbnail" />

                                    <div class="w-full">
                                        <a href="#"
                                            class="text-sm font-medium capitalize transition text-heading hover:underline">
                                            {{ item.item_name }}
                                        </a>
                                        <p v-if="item.item_variations.length !== 0" class="capitalize text-xs mb-1.5">
                                            <span v-for="(variation, index) in item.item_variations">
                                                {{ variation.variation_name }}: {{ variation.name
                                                }}<span v-if="index + 1 < item.item_variations.length">,&nbsp;</span>
                                            </span>
                                        </p>
                                        <h3 class="text-xs font-semibold">{{ item.total_currency_price }}</h3>
                                    </div>
                                </div>

                                <ul v-if="item.item_extras.length > 0 || item.instruction !== ''"
                                    class="flex flex-col gap-1.5 mt-2">
                                    <li class="flex gap-1" v-if="item.item_extras.length > 0">
                                        <h3 class="capitalize text-xs w-fit whitespace-nowrap">
                                            {{ $t("label.extras") }}:
                                        </h3>
                                        <p class="text-xs">
                                            <span v-for="(extra, index) in item.item_extras">
                                                {{ extra.name
                                                }}<span v-if="index + 1 < item.item_extras.length">,&nbsp;</span>
                                            </span>
                                        </p>
                                    </li>
                                    <li class="flex gap-1" v-if="item.instruction !== ''">
                                        <h3 class="capitalize text-xs w-fit whitespace-nowrap">
                                            {{ $t("label.instruction") }}:
                                        </h3>
                                        <p class="text-xs">{{ item.instruction }}</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12" v-if="order.status === enums.orderStatusEnum.REJECTED">
                <div class="db-card">
                    <div class="db-card-header">
                        <h3 class="db-card-title">{{ $t("label.reason") }}</h3>
                    </div>
                    <div class="db-card-body">
                        <p>{{ order.reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 sm:col-6">
        <div class="row">
            <div class="col-12">
                <div class="db-card p-1">
                    <ul class="flex flex-col gap-2 p-3 border-b border-dashed border-[#EFF0F6]">
                        <li class="flex items-center justify-between text-heading">
                            <span class="text-sm leading-6 capitalize">{{ $t("label.subtotal") }}</span>
                            <span class="text-sm leading-6 capitalize">{{
                                order.subtotal_currency_price
                            }}</span>
                        </li>
                        <li class="flex items-center justify-between text-heading" v-if="order.campaign_discount && parseFloat(order.campaign_discount) > 0">
                            <span class="text-sm leading-6 capitalize text-green-600">
                                {{ $t("label.campaign_discount") }}
                                <span v-if="campaignInfo" class="text-xs text-gray-500">
                                    ({{ campaignInfo.name }})
                                </span>
                            </span>
                            <span class="text-sm leading-6 capitalize text-green-600 font-semibold">
                                -{{ order.campaign_discount_currency_price }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between text-heading" v-if="order.discount && parseFloat(order.discount) > 0 && (!order.campaign_discount || parseFloat(order.discount) > parseFloat(order.campaign_discount))">
                            <span class="text-sm leading-6 capitalize">{{ $t("label.discount") }}</span>
                            <span class="text-sm leading-6 capitalize">{{
                                order.discount_currency_price
                            }}</span>
                        </li>
                        <li class="flex items-center justify-between text-heading" v-if="!order.dining_table_id || !order.table_name">
                            <span class="text-sm leading-6 capitalize">{{ $t("label.pickup_cost") }}</span>
                            <span class="text-sm leading-6 capitalize">{{
                                order.delivery_charge_currency_price
                            }}</span>
                        </li>
                    </ul>
                    <div class="flex items-center justify-between p-3">
                        <h4 class="text-sm leading-6 font-bold capitalize">
                            {{ $t("label.total") }}
                        </h4>
                        <h5 class="text-sm leading-6 font-bold capitalize">
                            {{ order.total_currency_price }}
                        </h5>
                    </div>
                </div>
            </div>
            <div class="col-12" v-if="!order.dining_table_id || !order.table_name">
                <div class="db-card">
                    <div class="db-card-header">
                        <h3 class="db-card-title">{{ $t("label.pickup_cost") }}</h3>
                    </div>
                    <div class="db-card-body">
                        <ul class="flex flex-col gap-3">
                            <li class="flex items-center gap-2.5">
                                <i class="lab lab-money lab-font-size-16"></i>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-heading">{{ pickupCostTypeLabel }}</span>
                                    <span class="text-xs text-gray-500">{{ order.delivery_charge_currency_price }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-12" v-if="order.campaign_id && campaignInfo">
                <div class="db-card">
                    <div class="db-card-header">
                        <h3 class="db-card-title">{{ $t("label.campaign") }}</h3>
                    </div>
                    <div class="db-card-body">
                        <ul class="flex flex-col gap-3">
                            <li class="flex items-center gap-2.5">
                                <i class="lab lab-offers lab-font-size-16 text-primary"></i>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-heading">{{ campaignInfo.name }}</span>
                                    <span class="text-xs text-gray-500" v-if="campaignInfo.type === 1">
                                        {{ $t("label.percentage_campaign") }}: {{ campaignInfo.discount_value }}%
                                    </span>
                                    <span class="text-xs text-gray-500" v-else-if="campaignInfo.type === 2">
                                        {{ $t("label.item_campaign") }}: {{ $t("label.buy_x_get_free", { count: campaignInfo.required_purchases }) }}
                                    </span>
                                </div>
                            </li>
                            <li class="flex items-center justify-between pt-2 border-t border-gray-200" v-if="order.campaign_discount && parseFloat(order.campaign_discount) > 0">
                                <span class="text-sm text-heading">{{ $t("label.campaign_discount_applied") }}</span>
                                <span class="text-sm font-semibold text-green-600">{{ order.campaign_discount_currency_price }}</span>
                            </li>
                            <li class="flex items-center justify-between pt-2 border-t border-gray-200" v-if="order.campaign_redeem_free_item_id">
                                <span class="text-sm text-heading">{{ $t("label.free_item_redeemed") }}</span>
                                <span class="text-sm font-semibold text-green-600">{{ $t("label.yes") }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import statusEnum from "../../../enums/modules/statusEnum";
import LoadingComponent from "../components/LoadingComponent";
import appService from "../../../services/appService";
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import isAdvanceOrderEnum from "../../../enums/modules/isAdvanceOrderEnum";
import paymentTypeEnum from "../../../enums/modules/paymentTypeEnum";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import alertService from "../../../services/alertService";
import TableOrderReasonComponent from "./TableOrderReasonComponent";
import TableOrderTokenComponent from "./TableOrderTokenComponent";
import TableOrderReceiptComponent from "./TableOrderReceiptComponent";

export default {
    name: "tableOrderShowComponent",
    components: {
        TableOrderReceiptComponent,
        LoadingComponent,
        TableOrderReasonComponent,
        TableOrderTokenComponent
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            payment_status: null,
            delivery_boy: null,
            order_status: null,
            enums: {
                isAdvanceOrderEnum: isAdvanceOrderEnum,
                paymentStatusEnum: paymentStatusEnum,
                orderStatusEnum: orderStatusEnum,
                paymentTypeEnum: paymentTypeEnum,
                orderTypeEnum: orderTypeEnum,
            },
        };
    },
    computed: {
        order: function () {
            return this.$store.getters["tableOrder/show"];
        },
        orderItems: function () {
            return this.$store.getters["tableOrder/orderItems"];
        },
        orderUser: function () {
            return this.$store.getters["tableOrder/orderUser"];
        },
        orderStatusObject: function () {
            return [
                { name: this.$t("label.accept"), value: orderStatusEnum.ACCEPT },
                { name: this.$t("label.preparing"), value: orderStatusEnum.PREPARING },
                { name: this.$t("label.prepared"), value: orderStatusEnum.PREPARED },
                { name: this.$t("label.delivered"), value: orderStatusEnum.DELIVERED },
            ];
        },
        orderStatusEnumArray: function () {
            return {
                [orderStatusEnum.PENDING]: this.$t("label.pending"),
                [orderStatusEnum.ACCEPT]: this.$t("label.accept"),
                [orderStatusEnum.PREPARING]: this.$t("label.preparing"),
                [orderStatusEnum.PREPARED]: this.$t("label.prepared"),
                [orderStatusEnum.DELIVERED]: this.$t("label.delivered"),
                [orderStatusEnum.REJECTED]: this.$t("label.rejected"),
            }
        },
        paymentStatusObject: function () {
            return [
                { name: this.$t("label.paid"), value: paymentStatusEnum.PAID },
                { name: this.$t("label.unpaid"), value: paymentStatusEnum.UNPAID },
            ];
        },
        paymentStatusEnumArray: function () {
            return {
                [paymentStatusEnum.PAID]: this.$t("label.paid"),
                [paymentStatusEnum.UNPAID]: this.$t("label.unpaid")
            }
        },
        orderTypeEnumArray: function () {
            return {
                [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
            }
        },
        paymentTypeEnumArray: function () {
            return {
                [paymentTypeEnum.CASH_ON_DELIVERY]: this.$t("label.cash_card"),
            }
        },
        pickupCostTypeLabel: function () {
            // Only show for online orders (not dining table orders)
            if (!this.order || (this.order.dining_table_id && this.order.table_name)) {
                return '';
            }
            
            // Debug: Check pickup_option value
            console.log('Order pickup_option:', this.order.pickup_option);
            console.log('Order delivery_charge:', this.order.delivery_charge);
            console.log('Order subtotal:', this.order.subtotal);
            
            // Determine pickup cost type based on stored pickup_option
            // First check if pickup_option is explicitly set
            if (this.order.pickup_option && this.order.pickup_option !== null && this.order.pickup_option !== '') {
                if (this.order.pickup_option === 'pickup_myself') {
                    return this.$t('label.pickup_myself');
                } else if (this.order.pickup_option === 'pay_to_driver') {
                    return this.$t('label.agree_with_driver');
                } else if (this.order.pickup_option === 'pay_for_pickup_cost_now') {
                    return this.$t('label.pay_for_pickup_cost_now');
                } else if (this.order.pickup_option === 'free_delivery') {
                    return this.$t('message.delivery_free_over_75');
                }
            }
            
            // Fallback: if pickup_option is not set (for old orders), try to determine from delivery_charge and subtotal
            const subtotal = parseFloat(this.order.subtotal) || 0;
            const deliveryCharge = parseFloat(this.order.delivery_charge) || 0;
            
            if (subtotal >= 80) {
                return this.$t('message.delivery_free_over_75');
            } else if (deliveryCharge === 5) {
                return this.$t('label.pay_for_pickup_cost_now');
            } else if (deliveryCharge === 0) {
                // Can't distinguish between pickup_myself and pay_to_driver for old orders, show both
                return this.$t('label.pickup_myself') + ' / ' + this.$t('label.agree_with_driver');
            }
            
            return '';
        },
        campaignInfo: function () {
            if (!this.order || !this.order.campaign_snapshot) {
                return null;
            }
            
            try {
                const snapshot = typeof this.order.campaign_snapshot === 'string' 
                    ? JSON.parse(this.order.campaign_snapshot) 
                    : this.order.campaign_snapshot;
                
                return {
                    id: snapshot.id,
                    name: snapshot.name,
                    type: snapshot.type, // 1 = percentage, 2 = item
                    discount_value: snapshot.discount_value,
                    required_purchases: snapshot.required_purchases,
                    free_item_id: snapshot.free_item_id,
                };
            } catch (e) {
                console.error('Error parsing campaign snapshot:', e);
                return null;
            }
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store
            .dispatch("tableOrder/show", this.$route.params.id)
            .then((res) => {
                this.payment_status = res.data.data.payment_status;
                this.delivery_boy = res.data.data.delivery_boy
                    ? res.data.data.delivery_boy.id
                    : 0;
                this.order_status = res.data.data.status;
                this.loading.isActive = false;
            })
            .catch((error) => {
                this.loading.isActive = false;
            });
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        changeStatus: function (status) {
            appService
                .acceptOrder()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch("tableOrder/changeStatus", {
                                id: this.$route.params.id,
                                status: status,
                            })
                            .then((res) => {
                                this.order_status = res.data.data.status;
                                this.loading.isActive = false;
                                alertService.successFlip(1, this.$t("label.status"));
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        changePaymentStatus: function (status) {
            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch("tableOrder/changePaymentStatus", {
                        id: this.$route.params.id,
                        payment_status: status,
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(1, this.$t("label.status"));
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
        orderStatus: function (status) {
            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch("tableOrder/changeStatus", {
                        id: this.$route.params.id,
                        status: status,
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(1, this.$t("label.status"));
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
    },
};
</script>