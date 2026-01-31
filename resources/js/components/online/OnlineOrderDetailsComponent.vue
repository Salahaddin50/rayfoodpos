<template>
    <LoadingComponent :props="loading" />
    <section class="pt-8 pb-16">
        <div class="container max-w-3xl">
            <router-link :to="{ name: 'online.menu' }"
                class="text-xs font-medium inline-flex mb-3 items-center gap-2 text-primary">
                <i class="lab lab-undo lab-font-size-16"></i>
                <span>{{ $t('label.back_to_menu') }}</span>
            </router-link>

            <div class="flex items-start flex-col md:flex-row gap-6">
                <div class="w-full">
                    <div class="p-4 mb-6 rounded-2xl shadow-xs bg-white">
                        <h3 class="text-sm leading-6 mb-1 font-medium">{{ $t("label.order_id") }}: <span
                                class="text-[#008BBA]">#{{ order.order_serial_no }}</span></h3>
                        <p class="text-xs font-light mb-3">{{ order.order_datetime }}</p>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="text-sm capitalize">{{ $t("label.order_type") }}:</span>
                            <span class="text-sm capitalize text-heading">
                                {{ $t('label.online_order') }}
                            </span>
                        </div>
                        <div v-if="order.whatsapp_number" class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="text-sm capitalize">{{ $t("label.whatsapp_number") }}:</span>
                            <span class="text-sm capitalize text-heading">
                                {{ order.whatsapp_number }}
                            </span>
                        </div>
                        <div v-if="order.token && order.token !== null && order.token !== ''" class="flex flex-wrap items-center gap-2 mb-5">
                            <span class="text-sm capitalize">{{ $t("label.token_no") }}:</span>
                            <span class="text-sm capitalize text-heading">
                                {{ order.token }}
                            </span>
                        </div>
                        <div v-else-if="!order.whatsapp_number" class="mb-5"></div>

                        <OrderStatusComponent :props="order" />

                        <div v-if="order && order.driver_id" class="flex items-center justify-center gap-2 mt-4 p-3 rounded-lg bg-primary/5 border border-primary/20">
                            <span class="text-sm font-medium text-heading">{{ $t('label.driver_assigned') }}:</span>
                            <span class="text-sm font-semibold text-primary">
                                <span v-if="order.driver_name">{{ order.driver_name }}</span>
                                <span v-if="order.driver_name && order.driver_whatsapp" class="mx-1">-</span>
                                <a v-if="order.driver_whatsapp" 
                                   :href="formatDriverWhatsAppLink(order.driver_whatsapp)" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="text-primary hover:underline">
                                    {{ formatWhatsAppNumber(order.driver_whatsapp) }}
                                </a>
                            </span>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button
                                type="button"
                                @click="refreshStatus"
                                :disabled="loading.isActive"
                                class="db-btn h-9 px-4 rounded-lg flex items-center justify-center gap-2 transition text-primary bg-primary/5 hover:bg-primary/10 disabled:opacity-60 disabled:cursor-not-allowed"
                                :title="$t('button.refresh_status')">
                                <i class="fa-solid fa-rotate-right text-sm"></i>
                                <span class="text-sm font-medium">{{ $t('button.refresh_status') }}</span>
                            </button>
                        </div>

                        <div>
                            <h3 class="font-medium mb-2">{{ orderBranch.name }}</h3>
                            <div class="flex items-center justify-between gap-5">
                                <div class="flex items-start justify-start gap-2.5">
                                    <i
                                        class="lab lab-location text-xs leading-none mt-1.5 flex-shrink-0 lab-font-size-14"></i>
                                    <span class="text-sm leading-6 text-heading">{{ orderBranch.address }}</span>
                                </div>
                                <div class="flex gap-2"
                                    v-if="parseInt(order.status) !== parseInt(enums.orderStatusEnum.REJECTED) && parseInt(order.status) !== parseInt(enums.orderStatusEnum.CANCELED)">
                                    <a :href="'tel:' + orderBranch.phone"
                                        class="w-8 h-8 rounded-full flex items-center justify-center bg-primary-light"
                                        :title="$t('label.call')">
                                        <i class="lab lab-call-calling font-fill-primary lab-font-size-16"></i>
                                    </a>
                                    <button
                                        type="button"
                                        @click="openWhatsApp"
                                        class="w-8 h-8 rounded-full flex items-center justify-center bg-green-100"
                                        :title="$t('label.whatsapp')">
                                        <i class="fa-brands fa-whatsapp text-green-600 text-base"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4" v-if="parseInt(order.status) === parseInt(enums.orderStatusEnum.REJECTED)">
                            <h3 class="capitalize font-medium text-sm leading-6 mb-2">{{ $t("label.reason") }}:</h3>
                            <p class="text-sm text-heading mb-2">{{ order.reason }}</p>
                        </div>
                    </div>

                    <div v-if="parseInt(order.status) !== parseInt(enums.orderStatusEnum.REJECTED) && parseInt(order.status) !== parseInt(enums.orderStatusEnum.CANCELED)"
                        class="p-4 rounded-2xl shadow-xs bg-white">
                        <h3 class="capitalize font-medium text-sm leading-6 mb-2">{{ $t("label.payment_info") }}</h3>
                        <ul class="flex flex-col gap-2 mb-2">
                            <li class="flex items-center gap-2">
                                <span class="capitalize text-sm leading-6">{{ $t("label.method") }}:</span>
                                <span v-if="order.transaction" class="capitalize text-sm leading-6 text-heading">
                                    {{ order.transaction.payment_method }} ({{ order.transaction.transaction_no }})
                                </span>
                                <span v-else-if="paymentMethod === 'digitalPayment'"
                                    class="capitalize text-sm leading-6 text-heading">
                                    {{ $t('label.digital_payment') }}
                                </span>
                                <span v-else class="capitalize text-sm leading-6 text-heading">
                                    {{ paymentTypeEnumArray[order.payment_method] }}
                                </span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="capitalize text-sm leading-6">{{ $t("label.status") }}:</span>
                                <span class="capitalize text-sm leading-6"
                                    :class="enums.paymentStatusEnum.PAID === order.payment_status ? 'text-green-600' : 'text-[#FB4E4E]'">
                                    {{ paymentStatusEnumArray[order.payment_status] }}
                                </span>
                            </li>
                        </ul>
                        <a v-if="order.payment_status === enums.paymentStatusEnum.UNPAID && paymentMethod === 'digitalPayment'"
                            :href="'/payment/' + order.id + '/pay'"
                            class="w-full rounded-3xl text-center font-medium leading-6 py-3 text-white bg-primary">
                            {{ $t('button.pay_now') }}
                        </a>
                    </div>
                </div>
                <div class="w-full rounded-2xl shadow-xs bg-white">
                    <div class="p-4 border-b">
                        <h3 class="font-medium text-sm leading-6 capitalize mb-4">{{ $t('label.order_details') }}</h3>
                        <div class="pl-3">
                            <div class="mb-3 pb-3 border-b last:mb-0 last:pb-0 last:border-b-0 border-gray-2"
                                v-if="orderItems.length > 0" v-for="item in orderItems" :key="item">
                                <div class="flex items-center gap-3 relative">
                                    <h3
                                        class="absolute top-5 -left-3 text-sm w-[26px] h-[26px] leading-[26px] text-center rounded-full text-white bg-heading">
                                        {{ item.quantity }}
                                    </h3>
                                    <img class="w-16 h-16 rounded-lg flex-shrink-0" :src="item.item_image"
                                        alt="thumbnail">
                                    <div class="w-full">
                                        <a href="#"
                                            class="text-sm font-medium capitalize transition text-heading hover:underline">
                                            {{ item.item_name }}
                                        </a>

                                        <p v-if="item.item_variations.length > 0" class="capitalize text-xs mb-1.5">
                                            <span v-for="variation in item.item_variations" :key="variation">
                                                <span class="capitalize text-xs w-fit whitespace-nowrap">
                                                    {{ variation.variation_name }}:&nbsp;
                                                </span>
                                                <span class="text-xs">
                                                    {{ variation.name }}
                                                </span>
                                            </span>
                                        </p>

                                        <h3 class="text-xs font-semibold">{{ item.total_currency_price }}</h3>
                                    </div>
                                </div>
                                <ul class="flex flex-col gap-1.5 mt-2">
                                    <li class="flex gap-1" v-if="item.item_extras.length > 0">
                                        <h3 class="capitalize text-xs w-fit whitespace-nowrap">
                                            {{ $t('label.extras') }}:
                                        </h3>
                                        <p class="text-xs" v-for="(extra, index) in item.item_extras">
                                            {{ extra.name }}<span v-if="index + 1 < item.item_extras.length">, </span>
                                        </p>
                                    </li>
                                    <li class="flex gap-1" v-if="item.instruction">
                                        <h3 class="capitalize text-xs w-fit whitespace-nowrap">
                                            {{ $t('label.instruction') }}:</h3>
                                        <p class="text-xs">{{ item.instruction }}</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="rounded-xl border border-[#EFF0F6]">
                            <ul class="flex flex-col gap-2 p-3 border-b border-dashed border-[#EFF0F6]">
                                <li class="flex items-center justify-between text-heading">
                                    <span class="text-sm leading-6 capitalize">{{ $t("label.subtotal") }}</span>
                                    <span class="text-sm leading-6 capitalize">
                                        {{ order.subtotal_currency_price }}
                                    </span>
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
                                    <span class="text-sm leading-6 capitalize">
                                        {{ order.discount_currency_price }}
                                    </span>
                                </li>
                                <li v-if="order.delivery_charge && parseFloat(order.delivery_charge) > 0" class="flex items-center justify-between text-heading">
                                    <span class="text-sm leading-6 capitalize">{{ $t("label.delivery_charge") }}</span>
                                    <span class="text-sm leading-6 capitalize">
                                        {{ order.delivery_charge_currency_price }}
                                    </span>
                                </li>
                            </ul>
                            <div class="flex items-center justify-between p-3">
                                <h4 class="text-sm leading-6 font-semibold capitalize">{{ $t("label.total") }}</h4>
                                <h5 class="text-sm leading-6 font-semibold capitalize">
                                    {{ order.total_currency_price }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t" v-if="order.campaign_id && campaignInfo">
                        <div class="rounded-xl border border-primary/20 bg-primary/5 p-3">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="lab lab-offers lab-font-size-16 text-primary"></i>
                                <h4 class="text-sm font-semibold text-heading">{{ $t("label.campaign") }}</h4>
                            </div>
                            <p class="text-sm font-medium text-heading mb-1">{{ campaignInfo.name }}</p>
                            <p class="text-xs text-gray-600" v-if="campaignInfo.type === 1">
                                {{ $t("label.percentage_campaign") }}: {{ campaignInfo.discount_value }}%
                            </p>
                            <p class="text-xs text-gray-600" v-else-if="campaignInfo.type === 2">
                                {{ $t("label.item_campaign") }}: {{ $t("label.buy_x_get_free", { count: campaignInfo.required_purchases }) }}
                            </p>
                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200" v-if="order.campaign_discount && parseFloat(order.campaign_discount) > 0">
                                <span class="text-xs text-heading">{{ $t("label.campaign_discount_applied") }}</span>
                                <span class="text-xs font-semibold text-green-600">{{ order.campaign_discount_currency_price }}</span>
                            </div>
                            <div class="flex items-center justify-between mt-1" v-if="order.campaign_redeem_free_item_id">
                                <span class="text-xs text-heading">{{ $t("label.free_item_redeemed") }}</span>
                                <span class="text-xs font-semibold text-green-600">{{ $t("label.yes") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <OrderReceiptComponent :order="order" :orderBranch="orderBranch" :orderItems="orderItems" />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import LoadingComponent from "../table/components/LoadingComponent.vue";
import OrderStatusComponent from "../table/components/OrderStatusComponent.vue";
import OrderReceiptComponent from "../table/order/OrderReceiptComponent.vue";
import orderTypeEnum from "../../enums/modules/orderTypeEnum";
import orderStatusEnum from "../../enums/modules/orderStatusEnum";
import paymentStatusEnum from "../../enums/modules/paymentStatusEnum";
import paymentTypeEnum from "../../enums/modules/paymentTypeEnum";
import activityEnum from "../../enums/modules/activityEnum";
import router from "../../router";
import appService from "../../services/appService";

export default {
    name: "OnlineOrderDetailsComponent",
    components: { OrderReceiptComponent, OrderStatusComponent, LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            refreshInterval: null,
            previousDriverId: null,
            audioElement: null,
            enums: {
                activityEnum: activityEnum,
                orderStatusEnum: orderStatusEnum,
                paymentStatusEnum: paymentStatusEnum,
            }
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        order: function () {
            return this.$store.getters['tableDiningOrder/show'];
        },
        orderBranch: function () {
            return this.$store.getters['tableDiningOrder/orderBranch'];
        },
        orderItems: function () {
            return this.$store.getters['tableDiningOrder/orderItems'];
        },
        paymentMethod: function () {
            return this.$store.getters['tableCart/paymentMethod'];
        },
        // Keep translations reactive when changing language
        orderTypeEnumArray() {
            return {
                [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
            };
        },
        paymentStatusEnumArray() {
            return {
                [paymentStatusEnum.PAID]: this.$t("label.paid"),
                [paymentStatusEnum.UNPAID]: this.$t("label.unpaid"),
            };
        },
        paymentTypeEnumArray() {
            return {
                [paymentTypeEnum.CASH_ON_DELIVERY]: this.$t("label.cash_card"),
                [paymentTypeEnum.E_WALLET]: this.$t("label.e_wallet"),
                [paymentTypeEnum.PAYPAL]: this.$t("label.paypal"),
            };
        },
        whatsappLink() {
            // Use branch phone number to send message TO the restaurant
            if (!this.orderBranch.phone || !this.order.order_serial_no) {
                return '#';
            }
            
            // Format phone number: remove leading 0 and add 994
            let phoneNumber = this.orderBranch.phone.replace(/[^\d+]/g, '');
            
            // Remove + sign for processing
            phoneNumber = phoneNumber.replace(/\+/g, '');
            
            // If starts with 0, replace with 994
            if (phoneNumber.startsWith('0')) {
                phoneNumber = '994' + phoneNumber.substring(1);
            } 
            // If starts with 994, keep as is
            else if (!phoneNumber.startsWith('994')) {
                // Otherwise add 994
                phoneNumber = '994' + phoneNumber;
            }
            
            // Build order items breakdown
            let itemsBreakdown = '';
            if (this.orderItems && this.orderItems.length > 0) {
                itemsBreakdown = '\n\n';
                this.orderItems.forEach(item => {
                    itemsBreakdown += `${item.item_name} x${item.quantity} - ${item.total_currency_price}\n`;
                });
                
                // Add total if available
                if (this.order.total && !isNaN(this.order.total)) {
                    const totalFormatted = this.currencyFormat(
                        parseFloat(this.order.total),
                        this.setting.site_digit_after_decimal_point || 2,
                        this.setting.site_default_currency_symbol || '₼',
                        this.setting.site_currency_position || 'left'
                    );
                    itemsBreakdown += `\n${this.$t('label.total')}: ${totalFormatted}`;
                }
            }
            
            // Create message with order details and breakdown
            let message = this.$t('message.whatsapp_order_message', {
                orderNumber: this.order.order_serial_no,
                branchName: this.orderBranch.name
            }) + itemsBreakdown;
            
            // Add location if available
            if (this.order.location_url) {
                message += `\n\n📍 ${this.$t('label.delivery_location')}: ${this.order.location_url}`;
            }
            
            // Use wa.me for better mobile compatibility
            return `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
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
                    free_item: snapshot.free_item || null, // Includes category info
                };
            } catch (e) {
                console.error('Error parsing campaign snapshot:', e);
                return null;
            }
        }
    },
    mounted() {
        this.loading.isActive = true;
        if (this.$route.params.id) {
            this.loading.isActive = true;
            this.$store.dispatch("tableDiningOrder/show", this.$route.params.id).then(res => {
                this.loading.isActive = false;
                // Initialize previous driver_id on first load
                if (this.order && this.order.driver_id) {
                    this.previousDriverId = this.order.driver_id;
                } else {
                    this.previousDriverId = null;
                }
                // Start auto-refresh every 1 minute
                this.startAutoRefresh();
            }).catch((error) => {
                this.loading.isActive = false;
                // If order not found, redirect to menu
                router.push({ name: 'online.menu' });
            });
        } else {
            router.push({ name: 'online.menu' });
        }
    },
    methods: {
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        openWhatsApp() {
            const link = this.whatsappLink;
            
            console.log('Opening WhatsApp with link:', link);
            console.log('Branch phone:', this.orderBranch.phone);
            console.log('Order number:', this.order.order_serial_no);
            
            if (link === '#' || !link) {
                console.error('WhatsApp link not available');
                alert('WhatsApp link is not available');
                return;
            }
            
            // Open WhatsApp in a new window/tab
            window.open(link, '_blank', 'noopener,noreferrer');
        },
        refreshStatus() {
            if (!this.$route.params.id) return;
            this.loading.isActive = true;
            this.$store
                .dispatch("tableDiningOrder/show", this.$route.params.id)
                .then(() => {
                    // Check if driver has been assigned
                    if (this.order) {
                        const currentDriverId = this.order.driver_id || null;
                        
                        // If driver was just assigned (changed from null/empty to a value), play sound
                        if (currentDriverId && 
                            (this.previousDriverId === null || this.previousDriverId === undefined || this.previousDriverId === '') &&
                            currentDriverId !== this.previousDriverId) {
                            console.log('Driver assigned - playing sound. Driver ID:', currentDriverId);
                            this.playRingingSound();
                        }
                        
                        // Update previous driver_id
                        this.previousDriverId = currentDriverId;
                    }
                    this.loading.isActive = false;
                })
                .catch(() => {
                    this.loading.isActive = false;
                });
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
                console.log('Playing ringing sound:', audioPath);
                
                // Play sound twice with a small gap between
                this.playSoundOnce(audioPath, 0);
                this.playSoundOnce(audioPath, 2000); // Play second time after 2 seconds
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
                    
                    // Stop after 3 seconds
                    setTimeout(() => {
                        audio.pause();
                        audio.currentTime = 0;
                    }, 3000);
                } catch (error) {
                    console.error('Error playing sound:', error);
                }
            }, delay);
        },
        startAutoRefresh() {
            // Clear any existing interval
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
            // Set interval to refresh every 1 minute (60000ms)
            this.refreshInterval = setInterval(() => {
                this.refreshStatus();
            }, 60000);
        },
        stopAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }
        },
        formatWhatsAppNumber: function (number) {
            if (!number) return '';
            // Remove all non-digit characters
            let phoneNumber = number.replace(/\D/g, '');
            // Handle case where number already has 994 followed by 0
            if (phoneNumber.startsWith('9940')) {
                phoneNumber = '994' + phoneNumber.substring(4);
            }
            // If starts with 0, replace with 994
            else if (phoneNumber.startsWith('0')) {
                phoneNumber = '994' + phoneNumber.substring(1);
            } 
            // If doesn't start with 994, add it
            else if (!phoneNumber.startsWith('994')) {
                phoneNumber = '994' + phoneNumber;
            }
            // Return formatted with + sign
            return '+' + phoneNumber;
        },
        formatDriverWhatsAppLink: function (number) {
            if (!number) return '#';
            // Get the formatted number without + sign for the URL
            const formattedNumber = this.formatWhatsAppNumber(number).replace('+', '');
            // Create WhatsApp link
            return `https://wa.me/${formattedNumber}`;
        }
    },
    beforeUnmount() {
        this.stopAutoRefresh();
        
        // Clean up audio element
        if (this.audioElement) {
            this.audioElement.pause();
            this.audioElement = null;
        }
        
        this.$store.dispatch("tableCart/resetPaymentMethod").then().catch();
    }
}
</script>

