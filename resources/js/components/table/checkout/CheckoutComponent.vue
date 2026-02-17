<template>
    <LoadingComponent :props="loading" />
    <section class="pt-8 pb-16 min-h-[800px]">
        <div class="container max-w-[965px]">
            <router-link :to="{ name: 'table.menu.table', params: { slug: this.$route.params.slug } }"
                class="text-xs font-medium inline-flex mb-3 items-center gap-2 text-primary">
                <i class="lab lab-undo lab-font-size-16"></i>
                <span>{{ $t('label.back_to_home') }}</span>
            </router-link>

            <div class="row">
                <div class="col-12 md:col-7">
                    <div class="mb-6 rounded-2xl shadow-xs bg-white">
                        <h3 class="capitalize font-medium p-4 border-b border-gray-100">{{ $t('label.table') }}</h3>
                        <p class="capitalize p-4 text-heading">{{ $t('label.inside') }} - {{ table.name }}</p>
                    </div>


                    <div class="mb-6 rounded-2xl shadow-xs bg-white">
                        <h3 class="capitalize font-medium p-4 border-b border-gray-100">{{ $t('label.payment_method') }}
                        </h3>
                        <ul class="p-4 flex flex-col gap-5">
                            <li class="flex items-center gap-1.5">
                                <div class="custom-radio">
                                    <input type="radio" id="cash" v-model="paymentMethod" value="cashCard"
                                        class="custom-radio-field">
                                    <span class="custom-radio-span border-gray-400"></span>
                                </div>
                                <label for="cash" class="db-field-label text-heading">{{ $t('label.cash_card')
                                    }}</label>
                            </li>
                            <li class="flex items-center gap-1.5 opacity-50 cursor-not-allowed">
                                <div class="custom-radio">
                                    <input type="radio" id="digital" v-model="paymentMethod" value="digitalPayment"
                                        class="custom-radio-field" disabled>
                                    <span class="custom-radio-span border-gray-400"></span>
                                </div>
                                <label for="digital" class="db-field-label text-gray-400">{{ $t('label.digital_payment') }} ({{ $t('label.coming_soon') }})</label>
                            </li>
                        </ul>
                    </div>

                    <div class="mb-6 rounded-2xl shadow-xs bg-white">
                        <h3 class="capitalize font-medium p-4 border-b border-gray-100">{{ $t('label.contact_information') }} ({{ $t('label.optional') }})</h3>
                        <div class="p-4">
                            <label for="whatsapp" class="db-field-label">{{ $t('label.whatsapp_number') }}</label>
                            <div class="flex gap-2">
                                <select 
                                    ref="countryCodeSelect"
                                    v-model="countryCode" 
                                    class="db-field-control w-32 flex-shrink-0"
                                    @change="updateWhatsAppNumber"
                                >
                                    <option v-for="country in countryCodes" :key="country.code" :value="country.dial_code">
                                        {{ country.flag }} {{ country.dial_code }}
                                    </option>
                                </select>
                                <input 
                                    type="tel" 
                                    id="whatsapp" 
                                    v-model="phoneNumber"
                                    @input="updateWhatsAppNumber"
                                    :placeholder="$t('label.enter_phone_number')"
                                    class="db-field-control flex-1"
                                    maxlength="15"
                                >
                            </div>
                            <small class="text-xs text-gray-500 mt-1 block">{{ $t('message.whatsapp_optional_hint') }}</small>
                        </div>
                    </div>

                    <button type="button"
                        class="hidden md:block w-full rounded-3xl capitalize font-medium leading-6 py-3 text-white bg-primary"
                        @click="orderSubmit">
                        {{ $t('button.place_order') }}
                    </button>
                </div>

                <div class="col-12 md:col-5">
                    <!-- Campaign Section -->
                    <div v-if="campaignStatus" class="mb-4 rounded-2xl shadow-xs bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200">
                        <div class="p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-lg">🎁</span>
                                <h3 class="font-semibold text-gray-900">{{ campaignStatus.campaign_name }}</h3>
                            </div>

                            <!-- Campaign Completed Message -->
                            <div v-if="campaignStatus.is_completed" class="text-center py-4">
                                <div class="w-16 h-16 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-3">
                                    <span class="text-3xl">🎉</span>
                                </div>
                                <p class="text-lg font-semibold text-green-600 mb-2">
                                    {{ $t('message.campaign_completed') }}
                                </p>
                                <p class="text-sm text-gray-600 mb-3">
                                    {{ $t('message.campaign_completed_description') }}
                                </p>
                                <p v-if="campaignStatus.free_item" class="text-sm text-gray-700 font-medium">
                                    {{ $t('message.you_received_free_item', { item: campaignStatus.free_item.name }) }}
                                </p>
                            </div>

                            <!-- Percentage Campaign -->
                            <div v-else-if="campaignStatus.type === 'percentage'">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">{{ $t('label.your_discount') }}</span>
                                    <span class="font-bold text-green-700 text-lg">{{ campaignStatus.discount_value }}% {{ $t('label.percent_off') }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">{{ $t('message.campaign_auto_applied') }}</p>
                            </div>

                            <!-- Item Campaign (Buy X Get 1 Free) - Active -->
                            <div v-else-if="campaignStatus.type === 'item' && !campaignStatus.is_completed">
                                <!-- Zero Progress - Encouraging Message -->
                                <div v-if="campaignStatus.current_progress === 0" class="text-center py-3">
                                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                        <p class="text-sm font-medium text-blue-900 leading-relaxed">
                                            {{ $t('message.campaign_start_encouragement', { 
                                                campaign: campaignStatus.campaign_name, 
                                                category: campaignStatus.free_item?.category_name || 'eligible' 
                                            }) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Has Progress - Show Circles -->
                                <div v-else>
                                    <!-- Progress Circles -->
                                    <div class="flex justify-center flex-wrap gap-2 mb-3">
                                        <div 
                                            v-for="n in campaignStatus.required_purchases" 
                                            :key="n"
                                            class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-xs font-medium transition-all"
                                            :class="n <= campaignStatus.current_progress ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 text-gray-400 bg-white'"
                                        >
                                            <span v-if="n <= campaignStatus.current_progress">✓</span>
                                            <span v-else>{{ n }}</span>
                                        </div>
                                    </div>
                                    <p class="text-center text-sm text-gray-600 mb-2">
                                        {{ campaignStatus.current_progress }} / {{ campaignStatus.required_purchases }} {{ $t('label.orders') }}
                                    </p>
                                    <p v-if="campaignStatus.free_item && campaignStatus.free_item.category_name" class="text-center text-xs text-blue-600 mb-3">
                                        <i class="lab lab-info-circle"></i>
                                        {{ $t('message.only_category_orders_count', { category: campaignStatus.free_item.category_name }) }}
                                    </p>

                                    <!-- Not Yet Entitled -->
                                    <div v-if="campaignStatus.rewards_available === 0 && campaignStatus.free_item" class="text-center">
                                        <p class="text-sm text-gray-700">
                                            {{ $t('message.order_x_more_to_get_free', { count: ordersNeededForReward, item: campaignStatus.free_item.name }) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Entitled! Can Redeem -->
                                <div v-if="campaignStatus.rewards_available > 0 && campaignStatus.free_item" class="bg-green-100 rounded-lg p-3 mt-2">
                                    <p class="text-sm font-medium text-green-800 mb-2">
                                        🎉 {{ $t('message.you_are_entitled_free_item', { item: campaignStatus.free_item.name }) }}
                                    </p>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            v-model="campaignRedeem" 
                                            class="w-5 h-5 rounded border-green-400 text-green-600 focus:ring-green-500"
                                        />
                                        <span class="text-sm text-green-900 font-medium">
                                            {{ $t('message.add_free_item_to_order') }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl shadow-xs bg-white">
                        <div class="p-4 border-b">
                            <h3 class="capitalize font-medium mb-3 text-center">
                                {{ $t('label.cart_summary') }}
                            </h3>
                            <div class="pl-3">
                                <div v-for="cart in carts"
                                    class="mb-3 pb-3 border-b last:mb-0 last:pb-0 last:border-b-0 border-gray-2">
                                    <div class="flex items-center gap-3 relative">
                                        <h3
                                            class="absolute top-5 ltr:-left-3 rtl:-right-3 text-sm w-[26px] h-[26px] leading-[26px] text-center rounded-full text-white bg-heading">
                                            {{ cart.quantity }}</h3>
                                        <img :src="cart.image" alt="thumbnail"
                                            class="w-16 h-16 rounded-lg flex-shrink-0">
                                        <div class="w-full">
                                            <span class="text-sm font-medium capitalize transition text-heading">
                                                {{ cart.name }}
                                            </span>
                                            <p v-if="Object.keys(cart.item_variations.variations).length !== 0"
                                                class="capitalize text-xs mb-1.5">
                                                <span
                                                    v-for="(variation, variationName, index) in cart.item_variations.names">
                                                    {{ variationName }}: {{ variation }}
                                                    <span
                                                        v-if="index + 1 < Object.keys(cart.item_variations.names).length">,
                                                        &nbsp;</span>
                                                </span>
                                            </p>
                                            <h4 class="text-xs font-semibold">
                                                {{
                                                    currencyFormat(cart.total, setting.site_digit_after_decimal_point,
                                                        setting.site_default_currency_symbol, setting.site_currency_position)
                                                }}
                                            </h4>
                                        </div>
                                    </div>
                                    <ul v-if="cart.item_extras.extras.length > 0 || cart.instruction !== ''"
                                        class="flex flex-col gap-1.5 mt-2">
                                        <li v-if="cart.item_extras.extras.length > 0" class="flex gap-1">
                                            <h3 class="capitalize text-xs w-fit whitespace-nowrap">
                                                {{ $t('label.extras') }}:
                                            </h3>
                                            <p class="text-xs">
                                                <span v-for="(extra, index) in cart.item_extras.names">
                                                    {{ extra }}
                                                    <span v-if="index + 1 < cart.item_extras.names.length">,
                                                        &nbsp;</span>
                                                </span>
                                            </p>
                                        </li>
                                        <li v-if="cart.instruction !== ''" class="flex gap-1">
                                            <h3 class="capitalize text-xs w-fit whitespace-nowrap">
                                                {{ $t('label.instruction') }}:
                                            </h3>
                                            <p class="text-xs">{{ cart.instruction }}</p>
                                        </li>
                                    </ul>
                                </div>
                                
                                <!-- Free Item (Campaign Reward) - Only show if campaign is active and not completed -->
                                <div v-if="campaignRedeem && campaignStatus && campaignStatus.free_item && !campaignStatus.is_completed" 
                                    class="mb-3 pb-3 border-b border-green-200 bg-green-50 rounded-lg p-2">
                                    <div class="flex items-center gap-3 relative">
                                        <h3 class="absolute top-2 ltr:-left-1 rtl:-right-1 text-xs w-[22px] h-[22px] leading-[22px] text-center rounded-full text-white bg-green-600">
                                            🎁
                                        </h3>
                                        <div class="w-10 h-10 rounded-lg flex-shrink-0 bg-green-100 flex items-center justify-center text-lg ml-4">
                                            🍕
                                        </div>
                                        <div class="w-full">
                                            <span class="text-sm font-medium capitalize text-green-800">
                                                {{ campaignStatus.free_item.name }}
                                            </span>
                                            <p class="text-xs text-green-600">{{ $t('label.free_campaign_reward') }}</p>
                                            <h4 class="text-xs font-semibold text-green-700">
                                                {{ currencyFormat(0, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="rounded-xl mb-6 border border-[#EFF0F6]">
                                <ul class="flex flex-col gap-2 p-3 border-b border-dashed border-[#EFF0F6]">
                                    <li class="flex items-center justify-between text-heading">
                                        <span class="text-sm leading-6 capitalize">
                                            {{ $t('label.subtotal') }}
                                        </span>
                                        <span class="text-sm leading-6 capitalize">
                                            {{
                                                currencyFormat(subtotal, setting.site_digit_after_decimal_point,
                                                    setting.site_default_currency_symbol, setting.site_currency_position)
                                            }}
                                        </span>
                                    </li>
                                    <li v-if="campaignStatus && campaignStatus.type === 'percentage' && campaignDiscountPreview > 0" 
                                        class="flex items-center justify-between text-green-600">
                                        <span class="text-sm leading-6 capitalize">
                                            {{ $t('label.campaign_discount') }}
                                        </span>
                                        <span class="text-sm leading-6 capitalize font-medium">
                                            -{{
                                                currencyFormat(campaignDiscountPreview, setting.site_digit_after_decimal_point,
                                                    setting.site_default_currency_symbol, setting.site_currency_position)
                                            }}
                                        </span>
                                    </li>
                                </ul>
                                <div class="flex items-center justify-between p-3">
                                    <h4 class="text-sm leading-6 font-semibold capitalize">
                                        {{ $t('label.total') }}
                                    </h4>
                                    <h5 class="text-sm leading-6 font-semibold capitalize">
                                        {{
                                            currencyFormat(displayTotal, setting.site_digit_after_decimal_point,
                                                setting.site_default_currency_symbol, setting.site_currency_position)
                                        }}
                                    </h5>
                                </div>
                            </div>
                            <button type="button"
                                class="block md:hidden w-full rounded-3xl capitalize font-medium leading-6 py-3 text-white bg-primary"
                                @click="orderSubmit">
                                {{ $t('button.place_order') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>


<script>

import LoadingComponent from "../../table/components/LoadingComponent.vue";
import appService from "../../../services/appService";
import sourceEnum from "../../../enums/modules/sourceEnum";
import _ from "lodash";
import OrderTypeEnum from "../../../enums/modules/orderTypeEnum";
import IsAdvanceOrderEnum from "../../../enums/modules/isAdvanceOrderEnum";
import router from "../../../router";
import alertService from "../../../services/alertService";
import axios from "axios";

export default {
    name: "CheckoutComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            placeOrderShow: false,
            paymentMethod: 'cashCard', // Default to cash payment
            countryCode: '+994', // Default to Azerbaijan
            phoneNumber: '',
            countryCodes: [
                { code: 'AZ', dial_code: '+994', flag: '🇦🇿', name: 'Azerbaijan' },
                { code: 'TR', dial_code: '+90', flag: '🇹🇷', name: 'Turkey' },
                { code: 'RU', dial_code: '+7', flag: '🇷🇺', name: 'Russia' },
                { code: 'US', dial_code: '+1', flag: '🇺🇸', name: 'United States' },
                { code: 'GB', dial_code: '+44', flag: '🇬🇧', name: 'United Kingdom' },
                { code: 'DE', dial_code: '+49', flag: '🇩🇪', name: 'Germany' },
                { code: 'FR', dial_code: '+33', flag: '🇫🇷', name: 'France' },
                { code: 'IT', dial_code: '+39', flag: '🇮🇹', name: 'Italy' },
                { code: 'ES', dial_code: '+34', flag: '🇪🇸', name: 'Spain' },
                { code: 'SA', dial_code: '+966', flag: '🇸🇦', name: 'Saudi Arabia' },
                { code: 'AE', dial_code: '+971', flag: '🇦🇪', name: 'United Arab Emirates' },
                { code: 'IN', dial_code: '+91', flag: '🇮🇳', name: 'India' },
                { code: 'CN', dial_code: '+86', flag: '🇨🇳', name: 'China' },
                { code: 'JP', dial_code: '+81', flag: '🇯🇵', name: 'Japan' },
            ],
            checkoutProps: {
                form: {
                    dining_table_id: null,
                    customer_id: 2,
                    branch_id: null,
                    subtotal: 0,
                    discount: 0,
                    delivery_charge: 0,
                    delivery_time: null,
                    total: 0,
                    order_type: OrderTypeEnum.DINING_TABLE,
                    is_advance_order: IsAdvanceOrderEnum.NO,
                    source: sourceEnum.WEB,
                    address_id: null,
                    whatsapp_number: "",
                    items: []
                }
            },
            campaignStatus: null,
            campaignRedeem: false,
        }
    },
    mounted() {
        if (this.$store.getters['tableCart/lists'].length === 0) {
            this.$router.push({ name: 'table.menu.table', params: { slug: this.$route.params.slug } });
        }
        
        // Fetch campaign status if phone number is already set
        if (this.checkoutProps.form.whatsapp_number) {
            this.$nextTick(() => {
                this.fetchCampaignStatus();
            });
        }
    },
    watch: {
        'table.branch_id': function() {
            // Fetch campaign status when table/branch is loaded
            if (this.checkoutProps.form.whatsapp_number) {
                this.fetchCampaignStatus();
            }
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        carts: function () {
            return this.$store.getters['tableCart/lists'];
        },
        subtotal: function () {
            return this.$store.getters['tableCart/subtotal'];
        },
        table: function () {
            return this.$store.getters['tableCart/table'];
        },
        campaignDiscountPreview: function () {
            if (!this.campaignStatus || this.campaignStatus.type !== 'percentage') return 0;
            const percent = parseFloat(this.campaignStatus.discount_value || 0);
            return (this.subtotal * percent) / 100;
        },
        displayTotal: function () {
            return Math.max(0, this.subtotal - this.campaignDiscountPreview);
        },
        ordersNeededForReward: function () {
            if (!this.campaignStatus || this.campaignStatus.type !== 'item') return 0;
            const required = parseInt(this.campaignStatus.required_purchases) || 8;
            const current = parseInt(this.campaignStatus.current_progress) || 0;
            return Math.max(0, required - current);
        }
    },
    methods: {
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        buildWhatsAppNumber: function (selectedCode, phoneDigits) {
            const codes = ['994', '966', '971', '91', '92', '90', '86', '81', '44', '49', '39', '34', '33', '7', '1'];
            for (const code of codes) {
                if (phoneDigits.startsWith(code) && phoneDigits.length >= code.length + 6) {
                    let rest = phoneDigits.substring(code.length);
                    if (rest.startsWith('0') && rest.length >= 6) rest = rest.substring(1);
                    return '+' + code + rest;
                }
            }
            return selectedCode + phoneDigits;
        },
        updateWhatsAppNumber: function () {
            this.phoneNumber = this.phoneNumber.replace(/[^\d]/g, '');
            const code = (this.$refs.countryCodeSelect && this.$refs.countryCodeSelect.value) || this.countryCode;
            this.checkoutProps.form.whatsapp_number = this.buildWhatsAppNumber(code, this.phoneNumber);
            this.fetchCampaignStatus();
        },
        fetchCampaignStatus: function () {
            const phone = this.checkoutProps.form.whatsapp_number;
            if (!this.table || !this.table.branch_id) {
                // Wait for table to be loaded
                setTimeout(() => {
                    if (this.table && this.table.branch_id) {
                        this.fetchCampaignStatus();
                    }
                }, 500);
                return;
            }
            const branchId = this.table.branch_id;
            
            if (!phone || phone.trim() === '' || !branchId) {
                this.campaignStatus = null;
                this.campaignRedeem = false;
                return;
            }

            // Normalize phone number
            let normalizedPhone = phone;
            if (normalizedPhone.startsWith('+9940')) {
                normalizedPhone = '+994' + normalizedPhone.substring(5);
            }

            axios.post('frontend/campaign/progress', { 
                phone: normalizedPhone, 
                branch_id: branchId 
            })
                .then((res) => {
                    if (res.data && res.data.status) {
                        this.campaignStatus = res.data.data;
                        this.campaignRedeem = false;
                    } else {
                        this.campaignStatus = null;
                        this.campaignRedeem = false;
                    }
                })
                .catch(() => {
                    this.campaignStatus = null;
                    this.campaignRedeem = false;
                });
        },
        orderSubmit: function () {
            if (!this.paymentMethod) {
                return alertService.error(this.$t('message.payment_method'));
            }

            this.loading.isActive = true;

            const ensureTable = () => {
                if (this.table && this.table.id && this.table.branch_id) {
                    return Promise.resolve(this.table);
                }
                return this.$store.dispatch('tableDiningTable/show', this.$route.params.slug).then((res) => {
                    this.$store.dispatch('tableCart/initTable', res.data.data).then().catch();
                    return res.data.data;
                });
            };

            ensureTable().then((table) => {
                if (!table || !table.id || !table.branch_id) {
                    this.loading.isActive = false;
                    alertService.error(this.$t('message.something_went_wrong'));
                    return this.$router.push({ name: 'table.menu.table', params: { slug: this.$route.params.slug } });
                }

                this.checkoutProps.form.dining_table_id = table.id;
                this.checkoutProps.form.branch_id = table.branch_id;
            const code = (this.$refs.countryCodeSelect && this.$refs.countryCodeSelect.value) || this.countryCode;
            const phone = (this.phoneNumber || '').replace(/\D/g, '');
            this.checkoutProps.form.whatsapp_number = this.buildWhatsAppNumber(code, phone);
            this.checkoutProps.form.subtotal = this.subtotal;
            
            // IMPORTANT: Do NOT send campaign discount in discount field
            // Backend will calculate and apply campaign discount automatically
            // Frontend discount calculation is only for preview/display
            this.checkoutProps.form.discount = 0; // Let backend handle campaign discounts
            
            // Send original subtotal - backend will apply discount and recalculate total
            this.checkoutProps.form.total = parseFloat(this.subtotal).toFixed(this.setting.site_digit_after_decimal_point);
            
            // Only allow redemption if campaign is active and not completed
            this.checkoutProps.form.campaign_redeem = !!(this.campaignRedeem && this.campaignStatus && !this.campaignStatus.is_completed);
            
            this.checkoutProps.form.items = [];
            _.forEach(this.carts, (item, index) => {
                let item_variations = [];
                if (Object.keys(item.item_variations.variations).length > 0) {
                    _.forEach(item.item_variations.variations, (value, index) => {
                        item_variations.push({
                            "id": value,
                            "item_id": item.item_id,
                            "item_attribute_id": index,
                        });
                    });
                }

                if (Object.keys(item.item_variations.names).length > 0) {
                    let i = 0;
                    _.forEach(item.item_variations.names, (value, index) => {
                        item_variations[i].variation_name = index;
                        item_variations[i].name = value;
                        i++;
                    });
                }

                let item_extras = [];
                if (item.item_extras.extras.length) {
                    _.forEach(item.item_extras.extras, (value) => {
                        item_extras.push({
                            id: value,
                            item_id: item.item_id,
                        });
                    });
                }

                if (item.item_extras.names.length) {
                    let i = 0;
                    _.forEach(item.item_extras.names, (value) => {
                        item_extras[i].name = value;
                        i++;
                    });
                }

                this.checkoutProps.form.items.push({
                    item_id: item.item_id,
                    item_price: item.convert_price,
                    branch_id: this.checkoutProps.form.branch_id,
                    instruction: item.instruction,
                    quantity: item.quantity,
                    discount: item.discount,
                    total_price: item.total,
                    item_variation_total: item.item_variation_total,
                    item_extra_total: item.item_extra_total,
                    item_variations: item_variations,
                    item_extras: item_extras
                });
            });
            this.checkoutProps.form.items = JSON.stringify(this.checkoutProps.form.items);

            this.$store.dispatch('tableDiningOrder/save', this.checkoutProps.form).then(orderResponse => {
                this.checkoutProps.form.subtotal = 0;
                this.checkoutProps.form.discount = 0;
                this.checkoutProps.form.delivery_charge = 0;
                this.checkoutProps.form.delivery_time = null;
                this.checkoutProps.form.total = 0;
                this.checkoutProps.form.items = [];

                this.$store.dispatch('tableCart/resetCart').then(res => {
                    this.loading.isActive = false;
                    this.$store.dispatch('tableCart/paymentMethod', this.paymentMethod).then().catch();
                    router.push({ name: "table.menu.table", params: { slug: this.table.slug }, query: { id: orderResponse.data.data.id } });
                }).catch();
            }).catch((err) => {
                this.loading.isActive = false;
                if (typeof err.response.data.errors === 'object') {
                    _.forEach(err.response.data.errors, (error) => {
                        alertService.error(error[0]);
                    });
                }
            });
            }).catch(() => {
                this.loading.isActive = false;
                alertService.error(this.$t('message.something_went_wrong'));
            });
        }
    },
    watch: {
        carts: {
            handler(newVal) {
                if (!newVal || newVal.length === 0) {
                    this.$router.push({
                        name: 'table.menu.table',
                        params: { slug: this.$route.params.slug }
                    });
                }
            },
            deep: true,
            immediate: true
        }
    }
}
</script>