<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t("menu.table_orders") }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent @click.prevent="handleSlide('table-order-filter')" />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div
                            class="dropdown-list db-card-filter-dropdown-list transition-all duration-300 scale-y-0 origin-top">
                            <PrintComponent :props="printObj" />
                            <ExcelComponent :method="xls" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-filter-div" id="table-order-filter">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="order_id" class="db-field-title after:hidden">{{
                                $t("label.order_id")
                                }}</label>
                            <input id="order_id" v-model="props.search.order_serial_no" type="text"
                                class="db-field-control" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">{{
                                $t("label.status")
                                }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.status" :options="[
                                    { id: enums.orderStatusEnum.PENDING, name: $t('label.pending') },
                                    { id: enums.orderStatusEnum.ACCEPT, name: $t('label.accept') },
                                    { id: enums.orderStatusEnum.PREPARING, name: $t('label.preparing') },
                                    { id: enums.orderStatusEnum.PREPARED, name: $t('label.prepared') },
                                    { id: enums.orderStatusEnum.DELIVERED, name: $t('label.delivered') },
                                    { id: enums.orderStatusEnum.REJECTED, name: $t('label.rejected') },
                                ]" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="user_id" class="db-field-title">
                                {{ $t("label.customer") }}
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="user_id"
                                v-model="props.search.user_id" :options="customers" label-by="name" value-by="id"
                                :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStartDate" class="db-field-title after:hidden">
                                {{ $t("label.date") }}
                            </label>
                            <Datepicker hideInputIcon autoApply :enableTimePicker="false" utc="false"
                                @update:modelValue="handleDate" v-model="props.form.date" range
                                :preset-ranges="presetRanges">
                                <template #yearly="{ label, range, presetDateRange }">
                                    <span @click="presetDateRange(range)">{{ label }}</span>
                                </template>
                            </Datepicker>
                        </div>

                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t("button.search") }}</span>
                                </button>
                                <button class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                    <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                    <span>{{ $t("button.clear") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="db-table-responsive">
                <table class="db-table stripe" id="print" :dir="direction">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t("label.order_id") }}</th>
                            <th class="db-table-head-th">{{ $t("label.order_type") }}</th>
                            <th class="db-table-head-th">{{ $t("label.place") }} / {{ $t("label.token") }}</th>
                            <th class="db-table-head-th">{{ $t("label.location") }}</th>
                            <th class="db-table-head-th" style="display: none;">{{ $t("label.customer") }}</th>
                            <th class="db-table-head-th">{{ $t("label.amount") }}</th>
                            <th class="db-table-head-th" style="width: 140px;">{{ $t("label.pickup_type_cost") }}</th>
                            <th class="db-table-head-th">{{ $t("label.date") }}</th>
                            <th class="db-table-head-th">{{ $t("label.status") }}</th>
                            <th class="db-table-head-th" style="width: 200px; min-width: 200px;">Driver</th>
                            <th class="db-table-head-th hidden-print" v-if="permissionChecker('table_orders_show') || permissionChecker('table_orders_edit') || permissionChecker('table_orders_delete')">
                                {{ $t("label.action") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="orders.length > 0">
                        <tr class="db-table-body-tr" v-for="order in orders" :key="order">
                            <td class="db-table-body-td">
                                {{ order.order_serial_no }}
                            </td>
                            <td class="db-table-body-td">
                                <span v-if="order.dining_table_id && order.table_name" :class="statusClass(order.order_type)">
                                    {{ enums.orderTypeEnumArray[order.order_type] }}
                                </span>
                                <span v-else class="db-table-badge text-blue-600 bg-blue-100">
                                    {{ $t('label.online_order') }}
                                </span>
                            </td>
                            <td class="db-table-body-td">
                                <span v-if="order.dining_table_id && order.table_name">{{ order.table_name }}</span>
                                <a v-else-if="order.whatsapp_number" 
                                   :href="formatWhatsAppLink(order.whatsapp_number)" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800 underline">
                                    {{ formatWhatsAppNumber(order.whatsapp_number) }}
                                </a>
                                <span v-else>-</span>
                                <span v-if="order.token"> / #{{ order.token }}</span>
                            </td>
                            <td class="db-table-body-td">
                                <div v-if="order.whatsapp_number && order.location_url" class="flex flex-col gap-1">
                                    <a :href="order.location_url" 
                                       target="_blank"
                                       class="text-green-600 hover:text-green-800 flex items-center gap-1">
                                        <i class="lab lab-location lab-font-size-16"></i>
                                        <span class="underline">{{ $t('label.view_map') }}</span>
                                    </a>
                                    <span v-if="order.distance" class="text-xs text-gray-600">
                                        {{ formatDistance(order.distance) }}
                                    </span>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="db-table-body-td" style="display: none;">
                                {{ textShortener(order.customer.name, 20) }}
                            </td>
                            <td class="db-table-body-td">{{ order.total_amount_price }}</td>
                            <td class="db-table-body-td" style="width: 140px;">
                                <div v-if="getPickupTypeLabel(order)" class="text-sm">
                                    <div class="font-medium">{{ getPickupTypeLabel(order) }}</div>
                                    <div v-if="order.delivery_charge_currency_price && parseFloat(order.delivery_charge || 0) > 0" class="text-gray-600">
                                        {{ order.delivery_charge_currency_price }}
                                    </div>
                                    <div v-else class="text-gray-400">₼0.00</div>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="db-table-body-td">
                                {{ order.order_datetime }}
                            </td>
                            <td class="db-table-body-td">
                                <span :class="[orderStatusClass(order.status), { 'ff-blink-pending': order.status === enums.orderStatusEnum.PENDING }]">
                                    {{ enums.orderStatusEnumArray[order.status] }}
                                </span>
                                <span :class="orderStatusClass(order.is_advance_order)"
                                    v-if="order.is_advance_order === enums.isAdvanceOrderEnum.YES">
                                    {{ $t("label.advance") }}
                                </span>
                            </td>
                            <td class="db-table-body-td" style="width: 200px; min-width: 200px;">
                                <template v-if="isDriverApplicable(order)">
                                    <vue-select
                                        class="db-field-control f-b-custom-select"
                                        :options="drivers"
                                        label-by="name"
                                        value-by="id"
                                        :closeOnSelect="true"
                                        :searchable="true"
                                        :clearOnClose="true"
                                        placeholder="--"
                                        :modelValue="order.driver_id"
                                        @update:modelValue="assignDriver(order, $event)"
                                        :disabled="order.status !== enums.orderStatusEnum.DELIVERED && order.status !== enums.orderStatusEnum.PREPARED && order.status !== enums.orderStatusEnum.PREPARING"
                                    />
                                </template>
                                <template v-else>
                                    <span class="text-gray-400">-</span>
                                </template>
                            </td>
                            <td class="db-table-body-td hidden-print" v-if="permissionChecker('table_orders_show') || permissionChecker('table_orders_edit') || permissionChecker('table_orders_delete')">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <SmIconViewComponent :link="'admin.table.order.show'" :id="order.id"
                                        v-if="permissionChecker('table_orders_show')" />
                                    <button
                                        v-if="permissionChecker('table_orders_edit')"
                                        type="button"
                                        @click="togglePaymentStatus(order)"
                                        :class="[
                                            'db-table-action',
                                            order.payment_status === enums.paymentStatusEnum.PAID
                                                ? 'bg-[#E0FFED] text-[#1AB759]'
                                                : 'bg-[#FFDADA] text-[#FB4E4E]'
                                        ]"
                                        :title="order.payment_status === enums.paymentStatusEnum.PAID ? $t('label.paid') : $t('label.unpaid')">
                                        <i class="lab lab-cash"></i>
                                        <span class="db-tooltip">
                                            {{ order.payment_status === enums.paymentStatusEnum.PAID ? $t('label.paid') : $t('label.unpaid') }}
                                        </span>
                                    </button>
                                    <button type="button"
                                        v-if="permissionChecker('table_orders_edit')"
                                        @click="changeStatusToDelivered(order.id)"
                                        :disabled="order.status === enums.orderStatusEnum.ACCEPT || order.status === enums.orderStatusEnum.PREPARING"
                                        :class="[
                                            'db-table-action',
                                            {
                                                'delivered-done': order.status === enums.orderStatusEnum.DELIVERED,
                                                'delivered-ready': order.status === enums.orderStatusEnum.PREPARED,
                                                'delivered-passive': order.status === enums.orderStatusEnum.ACCEPT || order.status === enums.orderStatusEnum.PREPARING
                                            }
                                        ]"
                                        :title="$t('label.delivered')">
                                        <i class="lab lab-tick-circle-2"></i>
                                        <span class="db-tooltip">{{ $t('label.delivered') }}</span>
                                    </button>
                                    <SmIconDeleteComponent @click="destroy(order.id)"
                                        v-if="permissionChecker('table_orders_delete')" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tbody class="db-table-body" v-else>
                        <tr class="db-table-body-tr">
                            <td class="db-table-body-td text-center" :colspan="permissionChecker('table_orders_show') || permissionChecker('table_orders_edit') || permissionChecker('table_orders_delete') ? 10 : 9">
                                <div class="p-4">
                                    <div class="max-w-[300px] mx-auto mt-2">
                                        <img class="w-full h-full" :src="ENV.API_URL + '/images/default/not-found.png'"
                                            alt="Not Found">
                                    </div>
                                    <span class="d-block mt-3 text-lg">{{ $t('message.no_data_available') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6"
                v-if="orders.length > 0">
                <PaginationSMBox :pagination="pagination" :method="list" />
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <PaginationTextComponent :props="{ page: paginationPage }" />
                    <PaginationBox :pagination="pagination" :method="list" />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from "vue";
import { endOfMonth, endOfYear, startOfMonth, startOfYear, subMonths } from "date-fns";
import statusEnum from "../../../enums/modules/statusEnum";
import isAdvanceOrderEnum from "../../../enums/modules/isAdvanceOrderEnum";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import sourceEnum from "../../../enums/modules/sourceEnum";
import ENV from '../../../config/env';
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";

export default {
    name: "TableOrderListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        SmIconDeleteComponent,
        SmIconViewComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        Datepicker,
    },
    setup() {
        const date = ref();

        const presetRanges = ref([
            { label: "Today", range: [new Date(), new Date()] },
            { label: "This month", range: [startOfMonth(new Date()), endOfMonth(new Date())] },
            {
                label: "Last month",
                range: [
                    startOfMonth(subMonths(new Date(), 1)),
                    endOfMonth(subMonths(new Date(), 1)),
                ],
            },
            { label: "This year", range: [startOfYear(new Date()), endOfYear(new Date())] },
            {
                label: "This year (slot)",
                range: [startOfYear(new Date()), endOfYear(new Date())],
                slot: "yearly",
            },
        ]);

        return {
            date,
            presetRanges,
        };
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                orderStatusEnum: orderStatusEnum,
                orderTypeEnum: orderTypeEnum,
                isAdvanceOrderEnum: isAdvanceOrderEnum,
                paymentStatusEnum: paymentStatusEnum,
                orderStatusEnumArray: {
                    [orderStatusEnum.PENDING]: this.$t("label.pending"),
                    [orderStatusEnum.ACCEPT]: this.$t("label.accept"),
                    [orderStatusEnum.PREPARING]: this.$t("label.preparing"),
                    [orderStatusEnum.PREPARED]: this.$t("label.prepared"),
                    [orderStatusEnum.OUT_FOR_DELIVERY]: this.$t("label.out_for_delivery"),
                    [orderStatusEnum.DELIVERED]: this.$t("label.delivered"),
                    [orderStatusEnum.CANCELED]: this.$t("label.canceled"),
                    [orderStatusEnum.REJECTED]: this.$t("label.rejected"),
                    [orderStatusEnum.RETURNED]: this.$t("label.returned"),
                },
                orderTypeEnumArray: {
                    [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                    [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                    [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
                },
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.table_orders"),
            },
            props: {
                form: {
                    date: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: "id",
                    order_by: "desc",
                    order_serial_no: "",
                    user_id: null,
                    order_type: orderTypeEnum.DINING_TABLE,
                    exceptSource: sourceEnum.POS,
                    status: null,
                    from_date: "",
                    to_date: "",
                },
            },
            ENV: ENV,
            autoRefreshInterval: null,
            previousOrderIds: [],
            previousPendingOrPreparedOrderIds: [],
            audioElement: null
        };
    },
    mounted() {
        this.list();
        this.$store.dispatch("user/lists", {
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE,
        });
        this.$store.dispatch("driver/lists", {
            paginate: 0,
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE,
        }).then().catch();

        // Listen for breadcrumb refresh button (Table Orders)
        window.addEventListener('rayfood:refresh-table-orders', this.onExternalRefresh);
        
        // Start auto-refresh every 60 seconds
        this.startAutoRefresh();
    },
    beforeUnmount() {
        window.removeEventListener('rayfood:refresh-table-orders', this.onExternalRefresh);
        this.stopAutoRefresh();
        
        // Clean up audio element
        if (this.audioElement) {
            this.audioElement.pause();
            this.audioElement = null;
        }
    },
    computed: {
        orders: function () {
            return this.$store.getters["tableOrder/lists"];
        },
        customers: function () {
            return this.$store.getters["user/lists"];
        },
        pagination: function () {
            return this.$store.getters["tableOrder/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["tableOrder/page"];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        drivers: function () {
            return this.$store.getters['driver/lists'];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
    },
    methods: {
        startAutoRefresh() {
            this.autoRefreshInterval = setInterval(() => {
                const pageNum = typeof this.paginationPage === 'number' ? this.paginationPage : 1;
                this.list(pageNum);
            }, 60000); // 60 seconds
        },
        stopAutoRefresh() {
            if (this.autoRefreshInterval) {
                clearInterval(this.autoRefreshInterval);
                this.autoRefreshInterval = null;
            }
        },
        onExternalRefresh() {
            const pageNum = typeof this.paginationPage === 'number' ? this.paginationPage : 1;
            this.list(pageNum);
        },
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        getPickupTypeLabel: function (order) {
            // Only show for online orders (not dining table orders)
            if (order.dining_table_id && order.table_name) {
                return null;
            }
            
            // Determine pickup cost type based on stored pickup_option
            if (order.pickup_option && order.pickup_option !== null && order.pickup_option !== '') {
                if (order.pickup_option === 'pickup_myself') {
                    return this.$t('label.pickup_myself');
                } else if (order.pickup_option === 'pay_to_driver') {
                    return this.$t('label.agree_with_driver');
                } else if (order.pickup_option === 'pay_for_pickup_cost_now') {
                    return this.$t('label.pay_for_pickup_cost_now');
                } else if (order.pickup_option === 'free_delivery') {
                    return this.$t('message.delivery_free_over_75');
                }
            }
            
            // Fallback: if pickup_option is not set (for old orders), try to determine from delivery_charge
            const deliveryCharge = parseFloat(order.delivery_charge || 0);
            const subtotal = parseFloat(order.subtotal || 0);
            
            if (subtotal >= 80) {
                return this.$t('message.delivery_free_over_75');
            } else if (deliveryCharge > 0) {
                return this.$t('label.pay_for_pickup_cost_now');
            } else if (deliveryCharge === 0 && order.whatsapp_number) {
                // For online orders with 0 charge, could be either pickup_myself or pay_to_driver
                return this.$t('label.pickup_myself') + ' / ' + this.$t('label.agree_with_driver');
            }
            
            return null;
        },
        handleSlide: function (id) {
            return appService.handleSlide(id);
        },
        search: function () {
            this.list();
        },
        handleDate: function (e) {
            if (e) {
                this.props.search.from_date = e[0];
                this.props.search.to_date = e[1];
            } else {
                this.props.form.date = null;
                this.props.search.from_date = null;
                this.props.search.to_date = null;
            }
        },
        clear: function () {
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.order_by = "desc";
            this.props.search.order_serial_no = "";
            this.props.search.status = null;
            this.props.search.order_type = orderTypeEnum.DINING_TABLE;
            this.props.search.from_date = "";
            this.props.search.to_date = "";
            this.props.search.user_id = null;
            this.props.form.date = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch("tableOrder/lists", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const freshOrders = res?.data?.data || [];
                    // Initialize previousOrderIds if not already set (first load)
                    if (this.previousOrderIds.length === 0 && freshOrders.length > 0) {
                        this.previousOrderIds = freshOrders.map(order => order.id);
                        const pendingOrPreparedOrders = freshOrders.filter(order =>
                            order.status === this.enums.orderStatusEnum.PENDING ||
                            order.status === this.enums.orderStatusEnum.PREPARED
                        );
                        this.previousPendingOrPreparedOrderIds = pendingOrPreparedOrders.map(order => order.id);
                        console.log('First load - initialized with', this.previousOrderIds.length, 'orders,', this.previousPendingOrPreparedOrderIds.length, 'with PENDING/PREPARED status');
                    } else {
                        // Check for new orders and play sound (only after first load)
                        this.checkForNewOrders(freshOrders);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        checkForNewOrders: function (freshOrders = null) {
            const ordersToCheck = freshOrders || this.orders || [];
            if (!ordersToCheck.length) {
                this.previousOrderIds = [];
                this.previousPendingOrPreparedOrderIds = [];
                return;
            }

            const currentOrderIds = ordersToCheck.map(order => order.id);
            const pendingOrPreparedOrders = ordersToCheck.filter(order =>
                order.status === this.enums.orderStatusEnum.PENDING ||
                order.status === this.enums.orderStatusEnum.PREPARED
            );
            const currentPendingOrPreparedOrderIds = pendingOrPreparedOrders.map(order => order.id);

            if (this.previousOrderIds.length > 0) {
                const newOrderIds = currentOrderIds.filter(id => !this.previousOrderIds.includes(id));
                const newPendingOrPreparedOrderIds = currentPendingOrPreparedOrderIds.filter(id =>
                    !this.previousPendingOrPreparedOrderIds.includes(id)
                );

                if (newPendingOrPreparedOrderIds.length > 0) {
                    this.playRingingSound();
                } else if (newOrderIds.length > 0) {
                    const newOrdersWithPendingOrPrepared = ordersToCheck.filter(order =>
                        newOrderIds.includes(order.id) &&
                        (order.status === this.enums.orderStatusEnum.PENDING ||
                         order.status === this.enums.orderStatusEnum.PREPARED)
                    );
                    if (newOrdersWithPendingOrPrepared.length > 0) {
                        this.playRingingSound();
                    }
                }
            }

            this.previousOrderIds = [...currentOrderIds];
            this.previousPendingOrPreparedOrderIds = [...currentPendingOrPreparedOrderIds];
        },
        playRingingSound: function () {
            try {
                // Stop any currently playing audio
                if (this.audioElement) {
                    this.audioElement.pause();
                    this.audioElement.currentTime = 0;
                    this.audioElement = null;
                }

                // Get audio file path from settings or use default
                const audioPath = this.setting?.notification_audio || '/audio/notification.mp3';
                
                // Play sound three times for longer alert (0ms, 2s, 4s)
                this.playSoundOnce(audioPath, 0);
                this.playSoundOnce(audioPath, 2000);
                this.playSoundOnce(audioPath, 4000);
            } catch (error) {
                console.error('Error in playRingingSound:', error);
            }
        },
        playSoundOnce: function (audioPath, delay) {
            setTimeout(() => {
                try {
                    const audio = new Audio(audioPath);
                    audio.volume = 1.0; // Maximum volume
                    audio.loop = false;
                    
                    audio.play().catch(err => {
                        console.error('Could not play notification sound:', err);
                    });
                    
                    // Stop after 6 seconds (allows longer audio files to play)
                    setTimeout(() => {
                        audio.pause();
                        audio.currentTime = 0;
                    }, 6000);
                } catch (error) {
                    console.error('Error playing sound:', error);
                }
            }, delay);
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch("tableOrder/export", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t("menu.table_orders");
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('tableOrder/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.table_orders'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    })
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch((err) => {
                this.loading.isActive = false;
            })
        },
        changeStatusToDelivered: function (id) {
            const order = this.orders.find(o => o.id === id);
            if (order && (order.status === this.enums.orderStatusEnum.DELIVERED || 
                order.status === this.enums.orderStatusEnum.ACCEPT || 
                order.status === this.enums.orderStatusEnum.PREPARING)) {
                return;
            }
            try {
                this.loading.isActive = true;
                this.$store.dispatch("tableOrder/changeStatus", {
                    id: id,
                    status: this.enums.orderStatusEnum.DELIVERED,
                }).then((res) => {
                    this.loading.isActive = false;
                    alertService.successFlip(
                        1,
                        this.$t("label.status")
                    );
                    this.list();
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
        togglePaymentStatus: function (order) {
            try {
                this.loading.isActive = true;
                const nextStatus =
                    order.payment_status === this.enums.paymentStatusEnum.PAID
                        ? this.enums.paymentStatusEnum.UNPAID
                        : this.enums.paymentStatusEnum.PAID;

                this.$store.dispatch("tableOrder/changePaymentStatus", {
                    id: order.id,
                    payment_status: nextStatus,
                }).then(() => {
                    this.loading.isActive = false;
                    this.list(this.paginationPage);
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err?.response?.data?.message ?? err);
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err?.response?.data?.message ?? err);
            }
        },
        isDriverApplicable(order) {
            // Driver assignment is only for online orders (orders without dining_table_id)
            // Dining table orders (orders with dining_table_id) should not have driver assignment
            if (!order) return false;
            
            // If order has dining_table_id, it's a dining table order - driver not applicable
            if (order.dining_table_id) return false;
            
            // For orders without dining_table_id (online orders), driver assignment is allowed
            // Takeaway, Delivery, or Online orders (with whatsapp_number) can have drivers
            return !!(order.order_type === this.enums.orderTypeEnum.TAKEAWAY || 
                     order.order_type === this.enums.orderTypeEnum.DELIVERY || 
                     order.whatsapp_number);
        },
        assignDriver(order, driverId) {
            if (!order) return;
            if (!this.isDriverApplicable(order)) return;
            if (order.status !== this.enums.orderStatusEnum.DELIVERED && order.status !== this.enums.orderStatusEnum.PREPARED) return;

            this.loading.isActive = true;
            this.$store.dispatch('tableOrder/assignDriver', { id: order.id, driver_id: driverId }).then((res) => {
                const data = res?.data?.data;
                order.driver_id = data?.driver_id ?? null;
                order.driver_name = data?.driver_name ?? null;
                this.loading.isActive = false;
                if (driverId) {
                    alertService.success(this.$t('message.driver_added') || 'Driver Added Successfully.');
                    // Open WhatsApp app/web with pre-filled message if link is available
                    let whatsappLink = data?.whatsapp_link;
                    
                    // If backend didn't provide WhatsApp link, generate it from driver's WhatsApp number
                    if (!whatsappLink && driverId) {
                        const driver = this.drivers.find(d => d.id === driverId);
                        if (driver && driver.whatsapp) {
                            // Build WhatsApp message like backend does
                            const orderType = order.whatsapp_number ? 'Online' : (order.order_type === this.enums.orderTypeEnum.DELIVERY ? 'Delivery' : 'Takeaway');
                            const messageParts = [
                                `You have been assigned a ${orderType} order.`,
                                `Order Number: ${order.order_serial_no}`
                            ];
                            if (order.token) {
                                messageParts.push(`Token Number: ${order.token}`);
                            }
                            if (order.whatsapp_number) {
                                messageParts.push(`Client WhatsApp: ${this.formatWhatsAppNumber(order.whatsapp_number)}`);
                            }
                            if (order.location_url) {
                                messageParts.push(`Location: ${order.location_url}`);
                            }
                            const message = messageParts.join('\n');
                            
                            // Generate WhatsApp link with message
                            const phoneNumber = this.formatWhatsAppNumber(driver.whatsapp).replace('+', '');
                            whatsappLink = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
                        }
                    }
                    
                    if (whatsappLink) {
                        console.log('Opening WhatsApp with link:', whatsappLink);
                        // Use setTimeout to avoid popup blocker, or create a click event
                        setTimeout(() => {
                            const link = document.createElement('a');
                            link.href = whatsappLink;
                            link.target = '_blank';
                            link.rel = 'noopener noreferrer';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }, 100);
                    } else {
                        console.warn('WhatsApp link not found in response and driver WhatsApp not available:', data);
                    }
                } else {
                    alertService.successFlip(null, this.$t('label.driver') || 'Driver');
                }
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err?.response?.data?.message ?? err);
                const pageNum = typeof this.paginationPage === 'number' ? this.paginationPage : 1;
                this.list(pageNum);
            });
        },
        formatWhatsAppNumber: function (number) {
            if (!number) return '';
            let digits = (number + '').replace(/\D/g, '');
            if (!digits) return number.trim();
            // Strip leading 0 after any known country code (same as backend): +860503531437 -> +86503531437, etc.
            const countryCodes = ['994', '966', '971', '91', '92', '90', '86', '81', '44', '49', '39', '34', '33', '7', '1'];
            for (const code of countryCodes) {
                if (digits.startsWith(code + '0') && digits.length > code.length + 1) {
                    digits = code + digits.substring(code.length + 1);
                    break;
                }
            }
            // Azerbaijan only: 9940 -> 994; or local 0 (no country stored, 10 digits) -> assume 994
            if (digits.startsWith('9940')) digits = '994' + digits.substring(4);
            else if (digits.startsWith('0') && digits.length <= 10) digits = '994' + digits.substring(1);
            return '+' + digits;
        },
        formatWhatsAppLink: function (number) {
            if (!number) return '#';
            const formattedNumber = this.formatWhatsAppNumber(number).replace('+', '');
            return `https://wa.me/${formattedNumber}`;
        },
        formatDistance: function (distance) {
            if (!distance || distance === null || distance === undefined) {
                return '';
            }
            
            const dist = parseFloat(distance);
            if (isNaN(dist)) {
                return '';
            }
            
            if (dist < 1) {
                return (dist * 1000).toFixed(0) + ' m';
            } else {
                return dist.toFixed(2) + ' km';
            }
        },
    },
};
</script>

<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}

.db-table-action.delivered-done {
    color: #1AB759;
    cursor: default;
}

.db-table-action.delivered-ready {
    color: #6E7191;
    cursor: pointer;
}

.db-table-action.delivered-ready:hover {
    color: #1AB759;
}

.db-table-action.delivered-passive {
    color: #D9DBE9;
    cursor: not-allowed;
    opacity: 0.6;
}

/* Blink the status badge when order is pending (subtle pulse). */
.ff-blink-pending {
    animation: ffPendingBlink 1.1s ease-in-out infinite;
}

@keyframes ffPendingBlink {
    0%, 100% {
        opacity: 1;
        filter: saturate(1);
    }
    50% {
        opacity: 0.55;
        filter: saturate(1.2);
    }
}

@media (prefers-reduced-motion: reduce) {
    .ff-blink-pending {
        animation: none;
    }
}
</style>