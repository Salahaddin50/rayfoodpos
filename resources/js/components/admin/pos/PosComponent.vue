<template>
    <LoadingComponent :props="loading" />
    <PoscustomerComponent v-on:onCustomverCreate="onCustomverCreate" />

    <div class="md:w-[calc(100%-340px)] lg:w-[calc(100%-320px)] xl:w-[calc(100%-377px)]">
        <form @submit.prevent="search"
            class="flex items-center w-full h-[38px] leading-[38px] mb-4 rounded-lg bg-white border-[#EFF0F6] border-t border-l border-b">
            <input type="text" v-model="props.search.name" :placeholder="$t('label.search_by_menu_item')"
                class="w-full px-5 rounded-tl-lg rounded-bl-lg placeholder:text-xs placeholder:font-rubik placeholder:text-[#A0A3BD]">
            <button @click="resetName" type="button" v-if="props.search.name"
                class="text-sm text-red-500 fa-regular fa-circle-xmark mr-4"></button>
            <button type="submit"
                class="flex-shrink-0 w-[38px] h-full text-center ltr:rounded-tr-lg ltr:rounded-br-lg rtl:rounded-tl-lg rtl:rounded-bl-lg bg-primary">
                <i class="lab lab-search-normal text-white"></i>
            </button>
        </form>

        <div class="swiper pos-menu-swiper mb-6" v-if="categories.length > 1">
            <Swiper dir="ltr" :speed="1000" slidesPerView="auto" :spaceBetween="16" class="menu-slides">
                <SwiperSlide class="!w-fit" v-for="(category, index) in categories" :key="category"
                    :class="category.id === props.search.item_category_id || (category.id === 0 && props.search.item_category_id === '') ? 'pos-group' : ''">
                    <router-link v-if="index === 0" to="#" @click.prevent="allCategory"
                        class="w-28 flex flex-col items-center text-center gap-4 py-4 px-3 rounded-lg border-b-2 border-transparent transition hover:bg-primary-light hover:border-primary bg-white">
                        <img class="h-7 drop-shadow-category" :src="category.thumb" alt="category">
                        <h3 class="text-xs leading-[16px] font-medium font-rubik">{{ category.name }}</h3>
                    </router-link>
                    <router-link v-else to="#" @click.prevent="setCategory(category.id)"
                        class="w-28 flex flex-col items-center text-center gap-4 py-4 px-3 rounded-lg border-b-2 border-transparent transition hover:bg-primary-light hover:border-primary bg-white">
                        <img class="h-7 drop-shadow-category" :src="category.thumb" alt="category">
                        <h3 class="text-xs leading-[16px] font-medium font-rubik">{{ category.name }}</h3>
                    </router-link>
                </SwiperSlide>
            </Swiper>
        </div>
        <ItemComponent :items="items" v-if="items.length > 0" />

        <div class="my-12" v-else-if="items.length === 0 && !props.search.name">
            <div class="max-w-[350px] mx-auto">
                <img class="w-full mb-8" :src="setting.image_order_not_found" alt="image_order_not_found">
            </div>
            <span class="w-full mb-4 text-center text-black">{{ $t('message.no_data_available') }}</span>
        </div>
        <div class="my-12" v-else-if="items.length === 0 && props.search.name">
            <div class="max-w-[250px] mx-auto">
                <img class="w-full mb-8" :src="setting.item_not_found" alt="item_not_found">
            </div>
            <span class="w-full mb-4 text-center text-black">{{ $t('message.no_items_found') }}</span>
        </div>
    </div>

    <div id="pos-cart"
        class="db-pos-cartDiv fixed top-0 ltr:right-0 rtl:left-0 w-full h-screen rounded-none z-50 md:z-10 md:top-[85px] ltr:md:right-5 rtl:md:left-5 md:w-[322px] lg:w-[305px] xl:w-[360px] md:h-[calc(100vh-85px)] md:rounded-lg overflow-y-auto thin-scrolling bg-white">
        <div class="p-4">
            <div class="md:hidden text-right mb-3">
                <button class="db-pos-cartCls" @click="closePosCart('pos-cart')">
                    <i class="lab-close-circle-line font-fill-danger lab-font-size-24"></i>
                </button>
            </div>
            <div class="flex gap-2  mb-3">
                <vue-select
                    class="db-field-control w-full flex-auto text-sm rounded-lg appearance-none text-heading border-[#D9DBE9]"
                    id="customer" v-model="checkoutProps.form.customer_id" :options="customers" label-by="name"
                    value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                    :placeholder="$t('label.select_customer')" :search-placeholder="$t('label.search_customer')" />

                <button data-modal="#customerModal" @click.prevent="addCustomer" type="button"
                    class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg text-white bg-primary">
                    <i class="lab lab-add-circle-line"></i>
                    <span class="capitalize text-sm font-bold">{{ $t('button.add') }}</span>
                </button>
            </div>
            <div class="db-field mb-3">
                <div class="flex gap-2">
                    <input v-on:keypress="onlyNumber($event)"
                        class="db-field-control text-sm rounded-lg appearance-none text-heading border-[#D9DBE9] flex-1" id="token"
                        v-model="checkoutProps.form.token" :placeholder="$t('label.token_no')" />
                    <button @click.stop.prevent="generateToken" type="button"
                        class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg text-white bg-primary">
                        <i class="lab lab-add-circle-line"></i>
                        <span class="capitalize text-sm font-bold">Number</span>
                    </button>
                    <button @click.stop.prevent="resetToken" type="button"
                        class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg text-white bg-primary">
                        <i class="lab lab-refresh-line"></i>
                        <span class="capitalize text-sm font-bold">Reset</span>
                    </button>
                </div>
            </div>

            <div class="db-field mb-3">
                <textarea
                    id="pos_note"
                    v-model="checkoutProps.form.pos_note"
                    rows="2"
                    class="db-field-control text-sm rounded-lg appearance-none text-heading border-[#D9DBE9] w-full"
                    :placeholder="'Note (for kitchen / print)'" />
            </div>

            <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                <h4 class="text-sm font-medium mb-3">{{ $t('label.select_order_type') }}</h4>

                <div class="db-field-radio-group gap-1 active-group">

                    <label @click="dineInOrder" ref="dineIn" for="dinein" data-dine="#dine"
                        class="!w-fit db-field-radio px-2.5 py-2 rounded-lg border border-[#F7F7FC] bg-[#F7F7FC] active">
                        <div class="custom-radio sm">
                            <input ref="dineInInput" type="radio" id="dinein" name="orderType"
                                :value="orderTypeEnums.dineIn" v-model="checkoutProps.form.order_type"
                                class="custom-radio-field" />
                            <span class="custom-radio-span"></span>
                        </div>
                        <h3 class="db-field-label text-sm text-heading">
                            {{ $t('label.dine_in') }}
                        </h3>
                    </label>
                    <label ref="takeAway" @click="takeAwayOrder" for="takeway"
                        class="!w-fit db-field-radio px-2.5 py-2 rounded-lg border border-[#F7F7FC] bg-[#F7F7FC]">
                        <div class="custom-radio sm">
                            <input ref="takeAwayInput" type="radio" id="takeway" name="orderType"
                                :value="orderTypeEnums.takeAway" v-model="checkoutProps.form.order_type"
                                class="custom-radio-field" />
                            <span class="custom-radio-span"></span>
                        </div>
                        <h3 class="db-field-label text-sm text-heading">
                            {{ $t('label.takeaway') }}
                        </h3>
                    </label>
                    <div v-if="checkoutProps.form.order_type === orderTypeEnums.takeAway" class="flex items-center gap-2 ml-2">
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0"
                            v-model="checkoutProps.form.delivery_charge"
                            :placeholder="$t('label.pickup_cost')"
                            class="db-field-control text-sm rounded-lg w-12 h-9 text-heading border-[#D9DBE9] focus:border-primary focus:ring-primary"
                        />
                    </div>
                </div>
                <div ref="dineInDiv" id="dine" class="h-auto hidden transition">
                    <div class="mt-3">
                        <div class="db-field flex-grow">
                            <vue-select
                                class="db-field-control text-sm rounded-lg appearance-none text-heading border-[#D9DBE9]"
                                id="diningtables" :options="diningtables" v-model="checkoutProps.form.dining_table_id"
                                value-by="id" label-by="name" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" :placeholder="$t('label.select_table')"
                                :search-placeholder="$t('label.search_table')" />
                        </div>
                    </div>
                </div>
                
                <div ref="takeAwayDiv" id="takeaway" class="h-auto hidden transition">
                    <div class="mt-3">
                        <div class="db-field flex-grow">
                            <vue-select
                                class="db-field-control text-sm rounded-lg appearance-none text-heading border-[#D9DBE9]"
                                id="takeawaytypes" :options="takeawayTypes" v-model="checkoutProps.form.takeaway_type_id"
                                value-by="id" label-by="name" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" :placeholder="$t('label.select_takeaway_type')"
                                :search-placeholder="$t('label.search_takeaway_type')" />
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <table class="w-full">
            <thead class="bg-primary-light">
                <tr class="h-9">
                    <th class="capitalize text-xs font-normal font-rubik text-left pl-3 text-heading"></th>
                    <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                        {{ $t('label.item') }}
                    </th>
                    <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                        {{ $t('label.qty') }}
                    </th>
                    <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                        {{ $t('label.price') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(cart, index) in carts">
                    <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6] rtl:pr-3">
                        <button @click.prevent="deleteCartItem(index)">
                            <i class="lab lab-trash-line-2 font-fill-danger"></i>
                        </button>
                    </td>
                    <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6]">
                        <h3 class="capitalize text-xs font-rubik text-[#2E2F38]">{{ cart.name }}</h3>
                        <p v-if="Object.keys(cart.item_variations.variations).length !== 0">
                            <span v-for="(variation, variationName, index) in cart.item_variations.names">
                                <span class="capitalize text-[10px] leading-4 font-rubik text-heading">{{
                                    variationName
                                    }}:
                                    &nbsp;</span>
                                <span class="capitalize text-[10px] leading-4 font-rubik">{{ variation }}
                                    <span v-if="index + 1 < cart.item_variations.names">, &nbsp;</span>
                                </span>
                            </span>
                        </p>
                        <ul v-if="cart.item_extras.extras.length > 0 || cart.instruction !== ''">
                            <li v-if="cart.item_extras.extras.length > 0" class="leading-4">
                                <span class="capitalize text-[10px] leading-4 font-rubik text-heading">
                                    {{ $t('label.extras') }}:
                                </span>
                                <p class="capitalize text-[10px] leading-4 font-rubik">
                                    <span v-for="(extra, index) in cart.item_extras.names">
                                        {{ extra }}
                                        <span v-if="index + 1 < cart.item_extras.extras.length">, &nbsp;</span>
                                    </span>
                                </p>
                            </li>
                            <li v-if="cart.instruction !== ''" class="leading-4">
                                <span class="capitalize text-[10px] leading-4 font-rubik text-heading">
                                    {{ $t('label.instruction') }}:
                                </span>
                                <span class="capitalize text-[10px] leading-4 font-rubik">
                                    {{ cart.instruction }}
                                </span>
                            </li>
                        </ul>
                    </td>
                    <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6]">
                        <div class="flex items-center indec-group">
                            <button @click.prevent="cartQuantityDecrement(index)"
                                :class="cart.quantity === 1 ? 'fa-trash-can' : 'fa-minus'"
                                class="fa-solid text-[10px] w-[18px] h-[18px] leading-4 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-minus"></button>
                            <input v-on:keypress="onlyNumber($event)" v-on:keyup="cartQuantityUp(index, $event)"
                                type="number" :value="cart.quantity"
                                class="text-center w-7 text-xs font-semibold text-heading indec-value">
                            <button @click.prevent="cartQuantityIncrement(index)"
                                class="fa-solid fa-plus text-[10px] w-[18px] h-[18px] leading4 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-plus"></button>
                        </div>
                    </td>
                    <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6] text-xs font-rubik text-heading">
                        {{
                            currencyFormat(cart.total, setting.site_digit_after_decimal_point,
                                setting.site_default_currency_symbol, setting.site_currency_position)
                        }}
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="p-4">
            <div class="flex h-[38px]" v-if="carts.length > 0">
                <div class="dropdown-group">
                    <button
                        class="flex items-center justify-start w-[120px] h-full text-sm font-rubik rounded-tl rounded-bl appearance-none border pl-3 text-heading border-[#EFF0F6] dropdown-btn">
                        <span class="flex-1 text-start" v-if="discountType === discountTypeEnum.PERCENTAGE">{{
                            $t("label.percentage") }}</span>
                        <span class="flex-1 text-start" v-else>{{ $t("label.fixed") }}</span>
                        <i class="lab lab-arrow-down-2 lab-font-size-17 mx-1"></i>
                    </button>
                    <ul
                        class="p-2 rounded-lg shadow-xl absolute top-10 ltr:right-0 rtl:left-0 z-10 bg-white transition-all duration-300 origin-top scale-y-0 dropdown-list w-full">
                        <li class="flex items-center gap-2 py-1 px-2.5 rounded-md cursor-pointer hover:bg-gray-100"
                            v-for="option in [
                                { name: $t('label.percentage'), value: discountTypeEnum.PERCENTAGE },
                                { name: $t('label.fixed'), value: discountTypeEnum.FIXED }
                            ]" :key="option" @click="selectDiscount(option.value)">
                            <span class="text-heading capitalize text-sm">{{ option.name }}</span>

                        </li>
                    </ul>
                </div>
                <input v-on:keypress="floatNumber($event)" v-model="discount" type="text"
                    :placeholder="$t('label.add_discount')"
                    class="w-full h-full border-t border-b px-3 border-[#EFF0F6]">
                <button @click.prevent="applyDiscount" type="submit"
                    class="flex-shrink-0 w-16 h-full text-sm font-medium font-rubik capitalize ltr:rounded-tr-lg ltr:rounded-br-lg rtl:rounded-tl-lg rtl:rounded-bl-lg  text-white bg-[#008BBA]">
                    {{ $t('button.apply') }}
                </button>
            </div>
            <ul class="flex flex-col gap-1.5 mb-4 mt-4">
                <li class="flex items-center justify-between">
                    <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                        {{ $t("label.sub_total") }}
                    </span>
                    <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                        {{
                            currencyFormat(subtotal, setting.site_digit_after_decimal_point,
                                setting.site_default_currency_symbol, setting.site_currency_position)
                        }}
                    </span>
                </li>
                <li v-if="checkoutProps.form.order_type === orderTypeEnums.takeAway && parseFloat(checkoutProps.form.delivery_charge || 0) > 0" class="flex items-center justify-between">
                    <span class="text-sm font-rubik capitalize leading-6">{{ $t("label.pickup_cost") }}</span>
                    <span class="text-sm font-rubik capitalize leading-6">{{
                        currencyFormat(parseFloat(checkoutProps.form.delivery_charge || 0),
                            setting.site_digit_after_decimal_point, setting.site_default_currency_symbol,
                            setting.site_currency_position)
                    }}</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-sm font-rubik capitalize leading-6">{{ $t("label.discount") }}</span>
                    <span class="text-sm font-rubik capitalize leading-6">{{
                        currencyFormat(posDiscount,
                            setting.site_digit_after_decimal_point, setting.site_default_currency_symbol,
                            setting.site_currency_position)
                    }}</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-sm font-medium font-rubik capitalize leading-6 text-[#2E2F38]">
                        {{ $t("label.total") }}
                    </span>
                    <span class="text-sm font-medium font-rubik capitalize leading-6 text-[#2E2F38]">
                        {{
                            currencyFormat(subtotal - posDiscount + parseFloat(checkoutProps.form.delivery_charge || 0),
                                setting.site_digit_after_decimal_point, setting.site_default_currency_symbol,
                                setting.site_currency_position)
                        }}
                    </span>
                </li>
            </ul>
            <div class="flex items-center justify-center gap-6" v-if="carts.length > 0">
                <button @click.prevent="resetCart"
                    class="capitalize text-sm font-medium leading-6 font-rubik w-full text-center rounded-3xl py-2 text-white bg-[#FB4E4E]">
                    {{ $t('button.cancel') }}
                </button>
                <button @click.prevent="orderSubmit"
                    class="capitalize text-sm font-medium leading-6 font-rubik w-full text-center rounded-3xl py-2 text-white bg-[#1AB759]">
                    {{ $t('button.order') }}
                </button>
            </div>
        </div>
    </div>

    <button @click="openPosCart('pos-cart')" type="button"
        class="db-pos-cartBtn fixed md:hidden bottom-0 z-10 left-0 w-full h-14 py-4 text-center flex items-center justify-center shadow-xl-top gap-3 bg-primary">
        <i class="lab lab-bag-2 lab-font-size-13 text-white"></i>
            <span class="text-base font-medium font-rubik text-white">
            {{ totalItems() }} {{ $t('label.items') }} - {{
                currencyFormat(subtotal - posDiscount + parseFloat(checkoutProps.form.delivery_charge || 0),
                    setting.site_digit_after_decimal_point, setting.site_default_currency_symbol,
                    setting.site_currency_position)
            }}
        </span>
    </button>

    <!--====================================
      PAYMENT MODAL PART START
  =====================================-->
    <PaymentComponent :props="checkoutProps" />
    <!--====================================
          PAYMENT MODAL PART END
      =====================================-->
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import 'vue3-carousel/dist/carousel.css';
import ItemComponent from "./ItemComponent";
import sourceEnum from "../../../enums/modules/sourceEnum";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import isAdvanceOrderEnum from "../../../enums/modules/isAdvanceOrderEnum";
import statusEnum from "../../../enums/modules/statusEnum";
import roleEnum from "../../../enums/modules/roleEnum";
import appService from "../../../services/appService";
import discountTypeEnum from "../../../enums/modules/discountTypeEnum";
import alertService from "../../../services/alertService";
import PaymentComponent from "./PaymentComponent";
import PoscustomerComponent from './PosCustomerComponent';
import posPaymentMethodEnum from "../../../enums/modules/posPaymentMethodEnum";
import { Swiper, SwiperSlide } from 'swiper/vue';
import _ from "lodash";
import 'swiper/css';

export default {
    name: "PosComponent",
    components: {
        LoadingComponent,
        ItemComponent,
        PoscustomerComponent,
        Swiper,
        SwiperSlide,
        PaymentComponent
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            order: {},
            discount: null,
            checkoutProps: {
                form: {
                    branch_id: null,
                    subtotal: 0,
                    token: "",
                    customer_id: null,
                    discount: 0,
                    delivery_charge: 0,
                    delivery_time: null,
                    total: 0,
                    order_type: orderTypeEnum.POS,
                    is_advance_order: isAdvanceOrderEnum.NO,
                    pos_payment_method: posPaymentMethodEnum.CASH,
                    pos_payment_note: '',
                    pos_note: '',
                    source: sourceEnum.POS,
                    address_id: null,
                    items: [],
                    dining_table_id: null,
                    takeaway_type_id: null,
                    delivery_charge: 0,
                    pos_received_amount: null,
                }
            },
            props: {
                search: {
                    paginate: 0,
                    lite: 1,
                    order_column: "category_sort",
                    order_type: "asc",
                    name: "",
                    item_category_id: "",
                    status: statusEnum.ACTIVE
                },
            },
            categoryProps: {
                paginate: 0,
                order_column: "sort",
                order_type: "asc",
                status: statusEnum.ACTIVE
            },
            settings: {
                itemsToShow: 6.2,
                wrapAround: false,
                snapAlign: "start"
            },
            breakpoints: {
                200: {
                    itemsToShow: 1.4,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                250: {
                    itemsToShow: 1.9,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                300: {
                    itemsToShow: 2.3,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                375: {
                    itemsToShow: 3,
                    wrapAround: true,
                    snapAlign: 'start',
                },
                540: {
                    itemsToShow: 4.3,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                700: {
                    itemsToShow: 5.2,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                768: {
                    itemsToShow: 3.2,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                830: {
                    itemsToShow: 3.6,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                900: {
                    itemsToShow: 4.3,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                960: {
                    itemsToShow: 5.3,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                1024: {
                    snapAlign: 'start',
                    itemsToShow: 3.5,
                    wrapAround: false,
                },
                1100: {
                    snapAlign: 'start',
                    itemsToShow: 4.1,
                    wrapAround: false,
                },
                1180: {
                    snapAlign: 'start',
                    itemsToShow: 4.8,
                    wrapAround: false,
                },
                1280: {
                    snapAlign: 'start',
                    itemsToShow: 5.2,
                    wrapAround: false,
                },
                1400: {
                    snapAlign: 'start',
                    itemsToShow: 5.8,
                    wrapAround: false,
                },
                1600: {
                    snapAlign: 'start',
                    itemsToShow: 6.8,
                    wrapAround: false,
                },
                1700: {
                    snapAlign: 'start',
                    itemsToShow: 7.8,
                    wrapAround: false,
                },
                1800: {
                    snapAlign: 'start',
                    itemsToShow: 8.8,
                    wrapAround: false,
                },
                1920: {
                    snapAlign: 'start',
                    itemsToShow: 9.8,
                    wrapAround: false,
                },
                2000: {
                    snapAlign: 'start',
                    itemsToShow: 10.8,
                    wrapAround: false,
                },
                2100: {
                    snapAlign: 'start',
                    itemsToShow: 11.8,
                    wrapAround: false,
                }
            },
            statusEnum: statusEnum,
            discountTypeEnum: discountTypeEnum,
            posPaymentMethodEnum: posPaymentMethodEnum,
            discountType: discountTypeEnum.PERCENTAGE,
            orderTypeEnums: {
                dineIn: orderTypeEnum.DINING_TABLE,
                takeAway: orderTypeEnum.TAKEAWAY
            },
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        categories: function () {
            return this.$store.getters["posCategory/lists"];
        },
        items: function () {
            return this.$store.getters["item/lists"];
        },
        takeawayTypes: function () {
            return this.$store.getters["takeawayType/lists"];
        },
        customers: function () {
            return this.$store.getters['user/lists'];
        },
        carts: function () {
            return this.$store.getters['posCart/lists'];
        },
        subtotal: function () {
            return this.$store.getters['posCart/subtotal'];
        },
        posDiscount: function () {
            return this.$store.getters['posCart/discount'];
        },
        diningtables: function () {
            return this.$store.getters["diningTable/lists"];
        },
    },
    mounted() {
        this.closeSidebar();
        this.$refs.dineIn.click();
        this.itemCategories();
        try {
            this.loading.isActive = true;
            this.$store.dispatch("defaultAccess/show").then((res) => {
                this.checkoutProps.form.branch_id = res.data.data.branch_id;
                this.itemList();
                
                // Check if we're editing an existing order
                const editOrderId = this.$route.query.edit_order_id;
                if (editOrderId) {
                    this.loadOrderForEdit(editOrderId);
                }
            }).catch((err) => {
                this.loading.isActive = false;
            });

            this.customerList();

            this.loading.isActive = true;
            this.$store.dispatch("company/lists").then((res) => {
                this.company.name = res.data.data.company_name;
                this.company.email = res.data.data.company_email;
                this.company.phone = res.data.data.company_phone;
                this.company.address = res.data.data.company_address;
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
            this.loading.isActive = true;
            this.$store.dispatch("diningTable/lists", {
                order_column: 'id',
                order_type: 'desc',
                status: statusEnum.ACTIVE,
            }).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
            
            this.loading.isActive = true;
            this.$store.dispatch("takeawayType/lists", {
                order_column: 'sort_order',
                order_type: 'asc',
                status: statusEnum.ACTIVE,
            }).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        } catch (err) {
            this.loading.isActive = false;
        }
    },
    methods: {
        generateToken: function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            if (!this.checkoutProps.form.branch_id) {
                alertService.error("Please select a branch first");
                return false;
            }
            
            this.loading.isActive = true;
            this.$store.dispatch('token/generate', {
                branch_id: this.checkoutProps.form.branch_id
            }).then((res) => {
                this.checkoutProps.form.token = res.data.data.token;
                this.loading.isActive = false;
                alertService.success("Token generated: " + res.data.data.token);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response?.data?.message || "Token generation failed");
            });
            
            return false;
        },
        resetToken: function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            if (!this.checkoutProps.form.branch_id) {
                alertService.error("Please select a branch first");
                return false;
            }
            
            if (!confirm("Are you sure you want to reset the token counter? Next token will start from 1.")) {
                return false;
            }
            
            this.loading.isActive = true;
            this.$store.dispatch('token/reset', {
                branch_id: this.checkoutProps.form.branch_id
            }).then((res) => {
                this.checkoutProps.form.token = "";
                this.loading.isActive = false;
                alertService.success("Token counter reset successfully");
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response?.data?.message || "Token reset failed");
            });
            
            return false;
        },
        onlyNumber: function (e) {
            return appService.onlyNumber(e);
        },
        floatNumber: function (e) {
            return appService.floatNumber(e);
        },
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        openPosCart: function (id) {
            return appService.openPosCart(id);
        },
        closePosCart: function (id) {
            return appService.closePosCart(id);
        },
        resetName: function () {
            this.props.search.name = "";
            this.itemList();
        },
        selectDiscount(value) {
            this.discountType = value;
        },
        search: function () {
            this.itemList();
        },
        customerList: function (id = null) {
            this.loading.isActive = true;
            this.$store.dispatch('user/lists', {
                order_column: 'id',
                order_type: 'asc',
                status: statusEnum.ACTIVE,
            }).then((res) => {
                if (id === null && res.data.data && res.data.data.length > 1 && res.data.data[1]) {
                    this.checkoutProps.form.customer_id = res.data.data[1].id;
                } else if (id !== null) {
                    this.checkoutProps.form.customer_id = id;
                }
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        allCategory: function () {
            this.props.search.name = "";
            this.props.search.item_category_id = "";
            this.itemList();
        },
        itemCategories: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch("posCategory/lists", this.categoryProps).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        itemList: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            if (this.checkoutProps.form.branch_id) {
                this.props.search.branch_id = this.checkoutProps.form.branch_id;
            }
            this.$store.dispatch("item/lists", this.props.search).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        setCategory: function (id) {
            this.props.search.item_category_id = id;
            this.itemList();
        },
        cartQuantityUp: function (id, e) {
            if (e.target.value > 0) {
                this.$store.dispatch('posCart/quantity', { id: id, status: e.target.value }).then().catch();
            }
        },
        cartQuantityIncrement: function (id) {
            this.$store.dispatch('posCart/quantity', { id: id, status: "increment" }).then().catch();
        },
        cartQuantityDecrement: function (id) {
            this.$store.dispatch('posCart/quantity', { id: id, status: "decrement" }).then().catch();
        },
        deleteCartItem: function (id) {
            this.$store.dispatch('posCart/deleteCartItem', { id: id, status: "decrement" }).then().catch();
        },
        applyDiscount: function () {
            if (this.discountType == discountTypeEnum.FIXED) {
                if (this.subtotal < this.discount) {
                    return alertService.error(this.$t('message.discount_fixed_error_message'));
                } else {
                    this.checkoutProps.form.discount = parseFloat(+this.discount).toFixed(this.setting.site_digit_after_decimal_point);
                    this.$store.dispatch('posCart/discount', this.checkoutProps.form.discount).then().catch();
                }

            } else {
                if (this.discount > 100) {
                    return alertService.error(this.$t('message.discount_error_message'));
                } else {

                    this.checkoutProps.form.discount = parseFloat((this.subtotal * this.discount) / 100).toFixed(this.setting.site_digit_after_decimal_point);
                    this.$store.dispatch('posCart/discount', this.checkoutProps.form.discount).then().catch();

                }
            }
        },
        resetCart: function () {
            this.$store.dispatch('posCart/resetCart').then(res => {
            }).catch();
        },
        orderSubmit: function () {
            this.loading.isActive = true;
            this.checkoutProps.form.subtotal = this.subtotal;
            // Normalize delivery_charge so backend numeric validation never receives "" (empty string)
            // Convert empty string, null, undefined, or invalid values to 0
            const deliveryChargeValue = this.checkoutProps.form.delivery_charge;
            const deliveryCharge = (deliveryChargeValue === '' || deliveryChargeValue === null || deliveryChargeValue === undefined) 
                ? 0 
                : Number.parseFloat(deliveryChargeValue) || 0;
            this.checkoutProps.form.delivery_charge = Number.isFinite(deliveryCharge) ? deliveryCharge : 0;
            this.checkoutProps.form.total = parseFloat(this.subtotal - this.checkoutProps.form.discount + deliveryCharge).toFixed(this.setting.site_digit_after_decimal_point);
            this.checkoutProps.form.items = [];
            this.checkoutProps.form.pos_payment_note = this.checkoutProps.form.pos_payment_method === posPaymentMethodEnum.CASH ?
                null : this.checkoutProps.form.pos_payment_note;
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

            this.loading.isActive = false;
            if (!this.checkoutProps.form.token) {
                return alertService.error(this.$t("message.token_field_required"));
            }
            if (this.checkoutProps.form.order_type === orderTypeEnum.DINING_TABLE && !this.checkoutProps.form.dining_table_id) {
                return alertService.error(this.$t("message.table_field_required"));
            }
            if (this.checkoutProps.form.order_type === orderTypeEnum.TAKEAWAY && !this.checkoutProps.form.takeaway_type_id) {
                return alertService.error("Please select a takeaway type");
            }
            appService.modalShow('#orderpayment');
        },
        totalItems: function () {
            if (this.carts.length > 0) {
                let totalItem = 0;
                this.carts.forEach(cart => {
                    totalItem += cart.quantity;
                });
                return totalItem;
            }
        },
        addCustomer: function () {
            appService.modalShow("#customerModal");
        },
        onCustomverCreate: function (customerId) {
            appService.modalHide();
            this.customerList(customerId);
        },
        closeSidebar: function () {
            this.$store.dispatch("globalState/set", { topSidebar: false });
            document?.querySelector(".db-sidebar")?.classList?.add("active");
            document?.querySelector(".db-main")?.classList?.add("expand");
        },
        dineInOrder: function () {
            this.checkoutProps.form.delivery_charge = 0;
            this.checkoutProps.form.takeaway_type_id = null;
            this.$refs.dineIn.classList.add('active');
            this.$refs.dineInDiv.classList.add('block');
            this.$refs.dineInDiv.classList.remove('hidden');
            this.$refs.takeAway.classList.remove('active');
            this.$refs.takeAwayDiv.classList.add('hidden');
            this.$refs.takeAwayDiv.classList.remove('block');
        },
        takeAwayOrder: function () {
            this.checkoutProps.form.dining_table_id = null;
            this.$refs.takeAway.classList.add('active');
            this.$refs.takeAwayDiv.classList.add('block');
            this.$refs.takeAwayDiv.classList.remove('hidden');
            this.$refs.dineIn.classList.remove('active');
            this.$refs.dineInDiv.classList.add('hidden');
            this.$refs.dineInDiv.classList.remove('block');
        },
        loadOrderForEdit: function (orderId) {
            this.loading.isActive = true;
            
            // First ensure customer list and branch are loaded
            Promise.all([
                this.$store.dispatch('user/lists', {
                    order_column: 'id',
                    order_type: 'asc',
                    status: statusEnum.ACTIVE,
                }),
                this.$store.dispatch("defaultAccess/show")
            ]).then(([customerRes, branchRes]) => {
                // Now load the order
                return this.$store.dispatch('posOrder/show', orderId).then((orderRes) => {
                    return { order: orderRes.data.data, branch: branchRes.data.data };
                });
            }).then(({ order, branch }) => {
                // Store order ID for update
                this.checkoutProps.form.order_id = order.id;
                this.checkoutProps.form.order_serial_no = order.order_serial_no;
                
                // Set branch_id (required for validation)
                this.checkoutProps.form.branch_id = branch.branch_id;
                
                // Set order details - ensure all required fields are set
                this.checkoutProps.form.customer_id = parseInt(order.user_id);
                this.checkoutProps.form.token = order.token ? String(order.token).replace(/\D/g, '') : '';
                this.checkoutProps.form.pos_note = order.pos_note || '';
                this.checkoutProps.form.discount = parseFloat(order.discount || 0);
                this.checkoutProps.form.delivery_charge = parseFloat(order.delivery_charge || 0);
                this.checkoutProps.form.order_type = parseInt(order.order_type);
                this.checkoutProps.form.dining_table_id = order.dining_table_id ? parseInt(order.dining_table_id) : null;
                this.checkoutProps.form.takeaway_type_id = order.takeaway_type_id ? parseInt(order.takeaway_type_id) : null;
                this.checkoutProps.form.pos_payment_method = parseInt(order.pos_payment_method || 1);
                this.checkoutProps.form.pos_payment_note = order.pos_payment_note || null;
                this.checkoutProps.form.pos_received_amount = order.pos_received_amount ? parseFloat(order.pos_received_amount) : null;
                this.checkoutProps.form.is_advance_order = parseInt(order.is_advance_order || 0);
                this.checkoutProps.form.source = parseInt(order.source || 1);
                
                // Ensure token is numeric and not empty (required for validation)
                if (!this.checkoutProps.form.token || this.checkoutProps.form.token === '') {
                    // Generate a new token if missing
                    this.generateToken();
                }
                
                // Set order type UI
                if (order.order_type === orderTypeEnum.DINING_TABLE) {
                    this.$refs.dineIn.click();
                } else {
                    this.$refs.takeAway.click();
                }
                
                // Clear cart first
                this.$store.dispatch('posCart/resetCart');
                
                // Load order items into cart
                if (order.order_items && Array.isArray(order.order_items) && order.order_items.length > 0) {
                    const cartItems = [];
                    order.order_items.forEach((orderItem) => {
                        // Skip if orderItem is null/undefined or missing required fields
                        if (!orderItem || !orderItem.item_id) {
                            console.warn('Skipping invalid order item:', orderItem);
                            return;
                        }
                        
                        // Parse price from currency string if needed
                        let itemPrice = 0;
                        if (orderItem.price) {
                            // Remove currency symbols and parse
                            itemPrice = parseFloat(String(orderItem.price).replace(/[^\d.-]/g, '')) || 0;
                        } else if (orderItem.item_price) {
                            itemPrice = parseFloat(orderItem.item_price) || 0;
                        }
                        
                        // Convert order item to cart format - matching posCart store structure
                        const cartItem = {
                            item_id: orderItem.item_id,
                            name: orderItem.item_name || '',
                            image: orderItem.item_image || '',
                            currency_price: orderItem.total_currency_price || '',
                            convert_price: itemPrice,
                            quantity: parseInt(orderItem.quantity || 1),
                            discount: parseFloat(String(orderItem.discount || 0).replace(/[^\d.-]/g, '')) || 0,
                            item_variation_total: parseFloat(orderItem.item_variation_total || 0),
                            item_extra_total: parseFloat(orderItem.item_extra_total || 0),
                            instruction: orderItem.instruction || '',
                            item_variations: {
                                variations: {},
                                names: {}
                            },
                            item_extras: {
                                extras: [],
                                names: []
                            }
                        };
                        
                        // Load variations - handle null/undefined and ensure array
                        if (orderItem.item_variations && Array.isArray(orderItem.item_variations) && orderItem.item_variations.length > 0) {
                            orderItem.item_variations.forEach((variation) => {
                                if (variation && variation.variation_name && variation.id) {
                                    cartItem.item_variations.variations[variation.variation_name] = variation.id;
                                    cartItem.item_variations.names[variation.variation_name] = variation.name || '';
                                }
                            });
                        }
                        
                        // Load extras - handle null/undefined and ensure array
                        if (orderItem.item_extras && Array.isArray(orderItem.item_extras) && orderItem.item_extras.length > 0) {
                            orderItem.item_extras.forEach((extra) => {
                                if (extra && extra.id) {
                                    cartItem.item_extras.extras.push(extra.id);
                                    cartItem.item_extras.names.push(extra.name || '');
                                }
                            });
                        }
                        
                        cartItems.push(cartItem);
                    });
                    
                    // Add all items to cart at once
                    if (cartItems.length > 0) {
                        try {
                            this.$store.dispatch('posCart/lists', cartItems);
                        } catch (cartError) {
                            console.error('Error adding items to cart:', cartError);
                            alertService.error('Error loading items into cart. Please try again.');
                            this.loading.isActive = false;
                            return;
                        }
                    }
                }
                
                this.loading.isActive = false;
                alertService.success('Order loaded for editing: #' + order.order_serial_no);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response?.data?.message || 'Failed to load order');
            });
        },
    },
    watch: {
        carts: {
            handler(newCarts) {
                if (!newCarts || newCarts.length === 0) {
                    this.discount = null;
                    this.discountType = discountTypeEnum.PERCENTAGE;
                    this.$nextTick(() => {
                        if (this.$refs.dineIn) {
                            this.$refs.dineIn.click();
                            if (this.customers && this.customers.length > 1 && this.customers[1]) {
                                this.checkoutProps.form.customer_id = this.customers[1].id;
                            }

                        }
                    });
                }
            },
            deep: true,
            immediate: true
        }
    },
}
</script>