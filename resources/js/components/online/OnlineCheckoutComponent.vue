<template>
    <LoadingComponent :props="loading" />
    <section class="pt-8 pb-16 min-h-[800px]">
        <div class="container max-w-[965px]">
            <router-link :to="{ name: 'online.menu', params: { branchId: this.$route.params.branchId } }"
                class="text-xs font-medium inline-flex mb-3 items-center gap-2 text-primary">
                <i class="lab lab-undo lab-font-size-16"></i>
                <span>{{ $t('label.back_to_home') }}</span>
            </router-link>

            <div class="row">
                <div class="col-12 md:col-7">
                    <div class="mb-6 rounded-2xl shadow-xs bg-white">
                        <h3 class="capitalize font-medium p-4 border-b border-gray-100">{{ $t('label.order_type') }}</h3>
                        <p class="capitalize p-4 text-heading">{{ $t('label.online_order') }}</p>
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
                        <h3 class="capitalize font-medium p-4 border-b border-gray-100">{{ $t('label.pickup_cost') }}</h3>
                        <div class="p-4">
                            <div v-if="subtotal >= parseFloat(setting.site_free_delivery_threshold || 80)" class="text-sm text-heading">
                                {{ $t('message.delivery_free_over_75') }}
                            </div>
                            <ul v-else-if="subtotal < parseFloat(setting.site_free_delivery_threshold || 80)" class="flex flex-col gap-4">
                                <li class="flex items-center gap-1.5">
                                    <div class="custom-radio">
                                        <input type="radio" id="pickup_myself" v-model="pickupOption" value="pickup_myself"
                                            class="custom-radio-field">
                                        <span class="custom-radio-span border-gray-400"></span>
                                    </div>
                                    <label for="pickup_myself" class="db-field-label text-heading flex-1">
                                        {{ $t('label.pickup_myself') }} - 
                                        <span class="font-semibold">{{ currencyFormat(0, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}</span>
                                    </label>
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <div class="custom-radio">
                                        <input type="radio" id="pay_to_driver" v-model="pickupOption" value="pay_to_driver"
                                            class="custom-radio-field">
                                        <span class="custom-radio-span border-gray-400"></span>
                                    </div>
                                    <label for="pay_to_driver" class="db-field-label text-heading flex-1">
                                        {{ $t('label.agree_with_driver') }} - 
                                        <span class="font-semibold">{{ currencyFormat(0, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}</span>
                                        <span class="text-xs text-gray-500 ml-1">{{ $t('label.you_will_pay_him') }}</span>
                                    </label>
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <div class="custom-radio">
                                        <input type="radio" id="pay_for_pickup_cost_now" v-model="pickupOption" value="pay_for_pickup_cost_now"
                                            class="custom-radio-field">
                                        <span class="custom-radio-span border-gray-400"></span>
                                    </div>
                                    <label for="pay_for_pickup_cost_now" class="db-field-label text-heading flex-1">
                                        {{ $t('label.pay_for_pickup_cost_now') }} - 
                                        <span class="font-semibold">{{ currencyFormat(pickupCost, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}</span>
                                        <span v-if="distanceFromBranch && pickupOption === 'pay_for_pickup_cost_now'" class="text-xs text-gray-500 ml-1">({{ distanceFromBranch }})</span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-6 rounded-2xl shadow-xs bg-white">
                        <h3 class="capitalize font-medium p-4 border-b border-gray-100">{{ $t('label.contact_information') }}</h3>
                        <div class="p-4">
                            <label for="whatsapp" class="db-field-label required">{{ $t('label.whatsapp_number') }}</label>
                            <div class="flex gap-2">
                                <select 
                                    v-model="countryCode" 
                                    class="db-field-control w-32 flex-shrink-0"
                                    :class="errors.whatsapp_number ? 'invalid' : ''"
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
                                    :class="errors.whatsapp_number ? 'invalid' : ''"
                                    maxlength="15"
                                >
                            </div>
                            <small v-if="errors.whatsapp_number" class="db-field-alert">{{ errors.whatsapp_number[0] }}</small>
                            <small v-else class="text-xs text-gray-500 mt-1 block">{{ $t('message.phone_number_format_hint') }}</small>
                            
                            <label for="location" class="db-field-label required mt-4">{{ $t('label.delivery_location') }}</label>
                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    id="location" 
                                    v-model="locationUrl"
                                    :placeholder="$t('label.paste_google_maps_link')"
                                    class="db-field-control flex-1"
                                    :class="errors.location_url ? 'invalid' : ''"
                                    readonly
                                >
                                <button 
                                    type="button"
                                    @click="getLocation"
                                    :disabled="loadingLocation"
                                    class="db-btn px-4 py-2 text-white bg-primary whitespace-nowrap"
                                    :class="loadingLocation ? 'opacity-50 cursor-not-allowed' : ''"
                                >
                                    <i class="lab lab-location text-base mr-1"></i>
                                    {{ loadingLocation ? $t('button.getting_location') : $t('button.add_location') }}
                                </button>
                            </div>
                            <small v-if="errors.location_url" class="db-field-alert">{{ errors.location_url[0] }}</small>
                            <small v-else class="text-xs text-gray-500 mt-1 block">{{ $t('message.location_required_hint') }}</small>
                            <small v-if="distanceFromBranch && locationUrl" class="text-xs text-primary mt-1 block font-medium">
                                <i class="lab lab-location text-xs mr-1"></i>
                                {{ $t('label.distance') }}: {{ distanceFromBranch }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-12 md:col-5">
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
                                    <li v-if="subtotal < parseFloat(setting.site_free_delivery_threshold || 80)" class="flex items-center justify-between text-heading">
                                        <span class="text-sm leading-6 capitalize">
                                            {{ $t('label.pickup_cost') }}
                                        </span>
                                        <span class="text-sm leading-6 capitalize">
                                            {{
                                                currencyFormat(pickupCost, setting.site_digit_after_decimal_point,
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
                                            currencyFormat(total, setting.site_digit_after_decimal_point,
                                                setting.site_default_currency_symbol, setting.site_currency_position)
                                        }}
                                    </h5>
                                </div>
                            </div>
                            <button type="button"
                                class="hidden md:block w-full rounded-3xl capitalize font-medium leading-6 py-3 text-white bg-primary"
                                @click="orderSubmit">
                                {{ $t('button.place_order') }}
                            </button>
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

import LoadingComponent from "../table/components/LoadingComponent.vue";
import appService from "../../services/appService";
import sourceEnum from "../../enums/modules/sourceEnum";
import _ from "lodash";
import OrderTypeEnum from "../../enums/modules/orderTypeEnum";
import IsAdvanceOrderEnum from "../../enums/modules/isAdvanceOrderEnum";
import router from "../../router";
import alertService from "../../services/alertService";

export default {
    name: "OnlineCheckoutComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            placeOrderShow: false,
            paymentMethod: 'cashCard', // Default to cash payment
            pickupOption: 'pickup_myself', // Default pickup option
            errors: {},
            countryCode: '+994', // Default to Azerbaijan
            phoneNumber: '',
            locationUrl: '',
            loadingLocation: false,
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
                { code: 'AE', dial_code: '+971', flag: '🇦🇪', name: 'UAE' },
                { code: 'IN', dial_code: '+91', flag: '🇮🇳', name: 'India' },
                { code: 'PK', dial_code: '+92', flag: '🇵🇰', name: 'Pakistan' },
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
                    location_url: "",
                    pickup_option: null, // Initialize pickup_option in form
                    items: []
                }
            },
        }
    },
    mounted() {
        if (this.$store.getters['tableCart/lists'].length === 0) {
            this.$router.push({ name: 'online.menu', params: { branchId: this.$route.params.branchId } });
        }
        // Load branches if not already loaded
        if (!this.branches || this.branches.length === 0) {
            this.$store.dispatch("frontendBranch/lists", { paginate: 0 });
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
        pickupCost: function () {
            // Get threshold and cost from settings
            const freeDeliveryThreshold = parseFloat(this.setting.site_free_delivery_threshold || 80);
            
            // If order is above threshold, pickup cost is 0
            if (this.subtotal >= freeDeliveryThreshold) {
                return 0;
            }
            
            // Calculate pickup cost based on selected option
            if (this.pickupOption === 'pickup_myself') {
                return 0;
            } else if (this.pickupOption === 'pay_to_driver') {
                return 0;
            } else if (this.pickupOption === 'pay_for_pickup_cost_now') {
                // Calculate cost based on distance
                return this.calculateDeliveryCostByDistance();
            }
            return 0;
        },
        total: function () {
            return parseFloat(this.subtotal) + parseFloat(this.pickupCost);
        },
        branches: function () {
            return this.$store.getters["frontendBranch/lists"];
        },
        currentBranch: function () {
            if (!this.branches || !this.$route.params.branchId) {
                return null;
            }
            return this.branches.find(branch => branch.id === parseInt(this.$route.params.branchId));
        },
        distanceFromBranch: function () {
            if (!this.locationUrl || !this.currentBranch) {
                return null;
            }
            
            // Extract coordinates from locationUrl (format: https://www.google.com/maps?q=lat,lng)
            const match = this.locationUrl.match(/q=([\d.-]+),([\d.-]+)/);
            if (!match) {
                return null;
            }
            
            const deliveryLat = parseFloat(match[1]);
            const deliveryLng = parseFloat(match[2]);
            
            // Get branch coordinates
            const branchLat = parseFloat(this.currentBranch.latitude);
            const branchLng = parseFloat(this.currentBranch.longitude);
            
            if (!branchLat || !branchLng || isNaN(deliveryLat) || isNaN(deliveryLng)) {
                return null;
            }
            
            // Calculate distance using Haversine formula
            const distance = this.calculateDistance(branchLat, branchLng, deliveryLat, deliveryLng);
            
            if (distance < 1) {
                return (distance * 1000).toFixed(0) + ' m';
            } else {
                return distance.toFixed(2) + ' km';
            }
        },
    },
    methods: {
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        updateWhatsAppNumber: function () {
            // Remove any non-digit characters except leading +
            this.phoneNumber = this.phoneNumber.replace(/[^\d]/g, '');
            // Combine country code with phone number
            this.checkoutProps.form.whatsapp_number = this.countryCode + this.phoneNumber;
        },
        calculateDeliveryCostByDistance: function () {
            // Get distance in km
            if (!this.distanceFromBranch) {
                // If no distance, use cost 1 as default
                return parseFloat(this.setting.site_delivery_cost_1 || 5);
            }
            
            // Extract numeric distance (remove "km" or "m")
            const distanceStr = this.distanceFromBranch.replace(/[^\d.]/g, '');
            let distance = parseFloat(distanceStr);
            
            // Convert meters to km if needed
            if (this.distanceFromBranch.includes('m')) {
                distance = distance / 1000;
            }
            
            // Get thresholds and costs
            const threshold1 = parseFloat(this.setting.site_delivery_distance_threshold_1 || 5);
            const threshold2 = parseFloat(this.setting.site_delivery_distance_threshold_2 || 10);
            const cost1 = parseFloat(this.setting.site_delivery_cost_1 || 5);
            const cost2 = parseFloat(this.setting.site_delivery_cost_2 || 8);
            const cost3 = parseFloat(this.setting.site_delivery_cost_3 || 12);
            
            // Calculate cost based on distance
            if (distance < threshold1) {
                return cost1;
            } else if (distance < threshold2) {
                // Use cost2 if set, otherwise cost1
                return cost2 || cost1;
            } else {
                // Use cost3 if set, otherwise cost2, otherwise cost1
                return cost3 || cost2 || cost1;
            }
        },
        calculateDistance: function (lat1, lon1, lat2, lon2) {
            // Haversine formula to calculate distance between two coordinates
            const R = 6371; // Radius of the Earth in kilometers
            const dLat = this.deg2rad(lat2 - lat1);
            const dLon = this.deg2rad(lon2 - lon1);
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const distance = R * c; // Distance in kilometers
            return distance;
        },
        deg2rad: function (deg) {
            return deg * (Math.PI / 180);
        },
        getLocation: function () {
            if (!navigator.geolocation) {
                alertService.error(this.$t('message.geolocation_not_supported'));
                return;
            }

            this.loadingLocation = true;
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    this.locationUrl = `https://www.google.com/maps?q=${lat},${lng}`;
                    this.loadingLocation = false;
                    alertService.success(this.$t('message.location_added'));
                },
                (error) => {
                    this.loadingLocation = false;
                    console.error('Location error:', error);
                    alertService.error(this.$t('message.location_permission_denied'));
                }
            );
        },
        orderSubmit: function () {
            this.errors = {};
            
            if (!this.paymentMethod) {
                return alertService.error(this.$t('message.payment_method'));
            }

            if (!this.checkoutProps.form.whatsapp_number || this.checkoutProps.form.whatsapp_number.trim() === '') {
                this.errors.whatsapp_number = [this.$t('message.whatsapp_number_required')];
                return alertService.error(this.$t('message.whatsapp_number_required'));
            }

            if (!this.locationUrl || this.locationUrl.trim() === '') {
                this.errors.location_url = [this.$t('message.location_required')];
                return alertService.error(this.$t('message.location_required'));
            }

            this.loading.isActive = true;

            this.checkoutProps.form.branch_id = parseInt(this.$route.params.branchId);
            this.checkoutProps.form.subtotal = this.subtotal;
            this.checkoutProps.form.delivery_charge = this.pickupCost; // Store pickup cost in delivery_charge field
            this.checkoutProps.form.total = parseFloat(this.total).toFixed(this.setting.site_digit_after_decimal_point);
            this.checkoutProps.form.items = [];
            
            // Set location URL (now mandatory)
            this.checkoutProps.form.location_url = this.locationUrl;
            
            // Set pickup option type
            // If subtotal >= threshold, it's free delivery regardless of selected option
            const freeDeliveryThreshold = parseFloat(this.setting.site_free_delivery_threshold || 80);
            if (this.subtotal >= freeDeliveryThreshold) {
                this.checkoutProps.form.pickup_option = 'free_delivery';
            } else {
                this.checkoutProps.form.pickup_option = this.pickupOption;
            }
            
            // Explicitly verify pickup_option is set
            console.log('=== PICKUP OPTION DEBUG ===');
            console.log('Selected pickupOption:', this.pickupOption);
            console.log('Subtotal:', this.subtotal);
            console.log('pickup_option being set to:', this.checkoutProps.form.pickup_option);
            console.log('Form pickup_option value:', this.checkoutProps.form.pickup_option);
            console.log('=== END DEBUG ===');
            
            console.log('Submitting order with data:', {
                branch_id: this.checkoutProps.form.branch_id,
                customer_id: this.checkoutProps.form.customer_id,
                whatsapp_number: this.checkoutProps.form.whatsapp_number,
                location_url: this.checkoutProps.form.location_url,
                subtotal: this.checkoutProps.form.subtotal,
                delivery_charge: this.checkoutProps.form.delivery_charge,
                pickup_option: this.checkoutProps.form.pickup_option,
                total: this.checkoutProps.form.total
            });
            
            // Log full form to verify
            console.log('Full form object being sent:', JSON.stringify(this.checkoutProps.form, null, 2));
            
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

            // Final verification before sending
            console.log('=== FINAL CHECK BEFORE SENDING ===');
            console.log('pickup_option in form:', this.checkoutProps.form.pickup_option);
            console.log('pickup_option exists?', 'pickup_option' in this.checkoutProps.form);
            console.log('All form keys:', Object.keys(this.checkoutProps.form));
            console.log('=== END FINAL CHECK ===');

            this.$store.dispatch('tableDiningOrder/save', this.checkoutProps.form).then(orderResponse => {
                this.checkoutProps.form.subtotal = 0;
                this.checkoutProps.form.discount = 0;
                this.checkoutProps.form.delivery_charge = 0;
                this.checkoutProps.form.delivery_time = null;
                this.checkoutProps.form.total = 0;
                this.checkoutProps.form.whatsapp_number = "";
                this.checkoutProps.form.items = [];

                this.$store.dispatch('tableCart/resetCart').then(res => {
                    this.loading.isActive = false;
                    this.$store.dispatch('tableCart/paymentMethod', this.paymentMethod).then().catch();
                    router.push({ 
                        name: "online.menu.branch", 
                        params: { branchId: this.$route.params.branchId }, 
                        query: { id: orderResponse.data.data.id } 
                    });
                }).catch();
            }).catch((err) => {
                this.loading.isActive = false;
                console.error('Order submission error:', err.response);
                console.error('Error data:', err.response?.data);
                console.error('Error message:', err.response?.data?.message);
                console.error('Validation errors:', err.response?.data?.errors);
                
                if (err.response && err.response.data) {
                    if (typeof err.response.data.errors === 'object') {
                        this.errors = err.response.data.errors;
                        console.error('Detailed validation errors:', this.errors);
                        _.forEach(err.response.data.errors, (error, field) => {
                            console.error(`Field "${field}" error:`, error);
                            alertService.error(error[0]);
                        });
                    } else if (err.response.data.message) {
                        console.error('Server message:', err.response.data.message);
                        alertService.error(err.response.data.message);
                    } else {
                        alertService.error(this.$t('message.something_went_wrong'));
                    }
                } else {
                    alertService.error(this.$t('message.something_went_wrong'));
                }
            });
        }
    },
    watch: {
        carts: {
            handler(newVal) {
                if (!newVal || newVal.length === 0) {
                    this.$router.push({
                        name: 'online.menu',
                        params: { branchId: this.$route.params.branchId }
                    });
                }
            },
            deep: true,
            immediate: true
        }
    }
}
</script>

