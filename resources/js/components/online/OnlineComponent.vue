<template>
    <section class="mb-16 mt-4">
        <div class="container">
            <LoadingComponent :props="loading" />

            <!-- Branch Selector -->
            <div class="mb-6 rounded-2xl shadow-xs bg-white p-4">
                <label class="text-sm capitalize mb-2 block text-heading font-medium">{{ $t('label.select_branch') }}</label>
                <vue-select 
                    class="db-field-control f-b-custom-select" 
                    v-model="selectedBranch" 
                    :options="branches" 
                    label-by="name" 
                    value-by="id"
                    :closeOnSelect="true" 
                    :searchable="true" 
                    :clearOnClose="false" 
                    placeholder="--"
                    @update:modelValue="onBranchChange"
                />
            </div>

            <!-- WhatsApp Number Input -->
            <div class="mb-6 rounded-2xl shadow-xs bg-white p-4" v-if="selectedBranch">
                <label class="text-sm capitalize mb-2 block text-heading font-medium">{{ $t('label.whatsapp') }}</label>
                <input 
                    v-model="whatsappNumber" 
                    type="text" 
                    class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]"
                    :placeholder="$t('label.whatsapp')"
                />
                <small class="text-xs text-gray-500 mt-1 block">{{ $t('message.whatsapp_order_notification') }}</small>
            </div>

            <div v-if="selectedBranch">
                <div class="swiper mb-7 menu-swiper" v-if="categories.length > 1">
                    <Swiper :speed="1000" slidesPerView="auto" :spaceBetween="16" class="menu-slides" dir="ltr">
                        <SwiperSlide class="!w-fit relative" v-for="(category, index) in categories" :key="category"
                            :class="category.id === itemProps.search.item_category_id || (category.id === 0 && itemProps.search.item_category_id === '') ? 'pos-group' : ''">
                            <router-link v-if="index === 0" to="" @click.prevent="allCategory(category)"
                                class="w-32 flex flex-col items-center text-center gap-4 p-3 rounded-2xl border-b-2 border-transparent transition hover:bg-primary-light bg-[#F7F7FC] overflow-hidden">
                                <img class="h-10 drop-shadow-category" :src="category.thumb" alt="category">
                                <h3
                                    class="w-full text-xs leading-[16px] whitespace-nowrap overflow-hidden text-ellipsis font-medium font-rubik">
                                    {{ category.name }}</h3>
                            </router-link>
                            <router-link v-else to="" @click.prevent="setCategory(category.id, category.slug)"
                                class="w-32 flex flex-col items-center text-center gap-4 p-3 rounded-2xl border-b-2 border-transparent transition hover:bg-primary-light bg-[#F7F7FC] overflow-hidden">
                                <img class="h-10 drop-shadow-category" :src="category.thumb" alt="category">
                                <h3
                                    class="w-full text-xs leading-[16px] whitespace-nowrap overflow-hidden text-ellipsis font-medium font-rubik">
                                    {{ category.name }}</h3>
                            </router-link>
                        </SwiperSlide>
                    </Swiper>
                </div>

                <div v-if="categories.length > 0" class="flex flex-wrap gap-3 w-full mb-5 veg-navs">
                    <button
                        :disabled="itemProps.property.type !== null && itemProps.property.type === enums.itemTypeEnum.VEG"
                        @click.prevent="itemProps.property.type === enums.itemTypeEnum.NON_VEG ? itemTypeReset() : itemTypeSet(enums.itemTypeEnum.NON_VEG)"
                        :class="itemProps.property.type === enums.itemTypeEnum.NON_VEG ? 'veg-active' : ''" type="button"
                        class="flex items-center gap-3 w-fit pl-3 pr-4 py-1.5 rounded-3xl transition hover:shadow-filter hover:bg-white bg-[#EFF0F6]">
                        <img :src="setting.image_vag" alt="category" class="h-6">
                        <span class="capitalize text-sm font-medium text-heading">{{ $t('label.frontend_non_veg') }}</span>
                        <i
                            class="lab-close-circle-line text-xl text-red-500 transition opacity-0 ltr:-ml-8 rtl:-mr-8 clear-item-type-filter font-fill-danger lab-font-size-24"></i>
                    </button>
                    <button
                        :disabled="itemProps.property.type !== null && itemProps.property.type === enums.itemTypeEnum.NON_VEG"
                        @click.prevent="itemProps.property.type === enums.itemTypeEnum.VEG ? itemTypeReset() : itemTypeSet(enums.itemTypeEnum.VEG)"
                        :class="itemProps.property.type === enums.itemTypeEnum.VEG ? 'veg-active' : ''" type="button"
                        class="flex items-center gap-3 w-fit pl-3 pr-4 py-1.5 rounded-3xl transition hover:shadow-filter hover:bg-white bg-[#EFF0F6]">
                        <img :src="setting.image_non_vag" alt="category" class="h-6">
                        <span class="capitalize text-sm font-medium text-heading">{{ $t('label.veg') }}</span>
                        <i
                            class="lab-close-circle-line text-xl text-red-500 transition opacity-0 ltr:-ml-8 rtl:-mr-8 font-fill-danger lab-font-size-24"></i>
                    </button>
                </div>

                <div v-if="Object.keys(category).length > 0" class="flex gap-4 items-center justify-between mb-6">
                    <h2 class="capitalize text-[26px] leading-[40px] font-semibold text-center sm:text-left text-primary">
                        {{ category.name }}
                    </h2>
                    <div class="flex items-center gap-3">
                        <button type="button" class="lab lab-row-vertical lab-font-size-20 text-xl"
                            v-on:click="itemProps.property.design = enums.itemDesignEnum.LIST"
                            :class="itemProps.property.design === enums.itemDesignEnum.LIST ? 'text-primary' : 'text-[#A0A3BD]'"></button>
                        <button type="button" class="lab lab-element-3 lab-font-size-20 text-xl"
                            v-on:click="itemProps.property.design = enums.itemDesignEnum.GRID"
                            :class="itemProps.property.design === enums.itemDesignEnum.GRID ? 'text-primary' : 'text-[#A0A3BD]'"></button>
                    </div>
                </div>
                <ItemComponent v-if="items.length > 0" :items="items" :type="itemProps.property.type"
                    :design="itemProps.property.design" />
                <div class="mt-12" v-else>
                    <div class="max-w-[250px] mx-auto">
                        <img class="w-full mb-8" :src="setting.image_order_not_found" alt="image_order_not_found">
                    </div>
                    <span class="w-full mb-4 text-center text-black">{{ $t('message.no_data_available') }}</span>
                </div>
            </div>

            <div v-else class="text-center py-12">
                <p class="text-lg text-gray-500">{{ $t('message.please_select_branch_to_start') }}</p>
            </div>
        </div>
    </section>
    
    <div v-if="Object.keys(order).length > 0" ref="confirmOrder" id="confirm-order"
        class="modal confirm-order ff-modal">
        <div class="modal-dialog max-w-[360px] relative">
            <button class="modal-close fa-regular fa-circle-xmark absolute top-5 right-5"
                @click.prevent="closeModal"></button>
            <div class="modal-body">
                <h3 class="capitalize text-base font-medium text-center mt-2 mb-3">
                    {{ $t('message.order_thank_you') }}
                </h3>
                <img class="w-[120px] mx-auto mb-3" :src="setting.image_confirm" alt="gif">
                <h3 class="capitalize text-lg font-medium text-center mb-3 text-primary">
                    {{ $t('label.order_confirmed') }}
                </h3>
                <p class="text-sm leading-6 mb-4">
                    {{ $t('message.online_order_confirm') }}
                </p>

                <router-link @click.prevent="closeModal"
                    class="w-full rounded-3xl text-center font-medium leading-6 py-3 text-white bg-primary block"
                    :to="{ name: 'online.order.details', params: { id: order.id } }">
                    {{ $t('button.go_to_order') }}
                </router-link>

            </div>
        </div>
    </div>

</template>

<script>
import LoadingComponent from "../table/components/LoadingComponent.vue";
import statusEnum from "../../enums/modules/statusEnum";
import ItemComponent from "../table/components/ItemComponent.vue";
import itemDesignEnum from "../../enums/modules/itemDesignEnum";
import itemTypeEnum from "../../enums/modules/itemTypeEnum";
import orderTypeEnum from "../../enums/modules/orderTypeEnum";
import activityEnum from "../../enums/modules/activityEnum";
import paymentStatusEnum from "../../enums/modules/paymentStatusEnum";
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';

export default {
    name: "OnlineComponent",
    components: {
        ItemComponent,
        LoadingComponent,
        Swiper,
        SwiperSlide,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            selectedBranch: null,
            whatsappNumber: '',
            category: {
                id: 0,
                name: this.$t('label.all') + ' ' + this.$t('label.items')
            },
            categoryProps: {
                search: {
                    paginate: 0,
                    order_column: "sort",
                    order_type: "asc",
                    status: statusEnum.ACTIVE
                },
            },
            itemProps: {
                search: {
                    paginate: 0,
                    order_column: "id",
                    order_type: "asc",
                    item_category_id: ""
                },
                property: {
                    design: itemDesignEnum.LIST,
                    type: null
                }
            },
            enums: {
                activityEnum: activityEnum,
                paymentStatusEnum: paymentStatusEnum,
                itemTypeEnum: itemTypeEnum,
                itemDesignEnum: itemDesignEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                    [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                    [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table")
                },
            }
        }
    },
    computed: {
        branches: function () {
            return this.$store.getters["frontendBranch/lists"];
        },
        categories: function () {
            return this.$store.getters["tableItemCategory/lists"];
        },
        items: function () {
            return this.$store.getters["frontendItem/lists"];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        order: function () {
            return this.$store.getters['tableDiningOrder/show'];
        },
        paymentMethod: function () {
            return this.$store.getters['tableCart/paymentMethod'];
        }
    },
    mounted() {
        this.loading.isActive = true;
        
        // Load branches
        this.$store.dispatch("frontendBranch/lists", {
            paginate: 0,
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE
        }).then(() => {
            this.loading.isActive = false;
        }).catch(() => {
            this.loading.isActive = false;
        });

        // Load categories
        this.$store.dispatch("tableItemCategory/lists", this.categoryProps.search).then().catch();

        // Store whatsapp in tableCart for checkout
        this.$store.dispatch('tableCart/setWhatsapp', '').then().catch();

        // Check if returning from order placement
        if (Object.keys(this.$route.query).length > 0 && this.$route.query.id) {
            this.loading.isActive = true;
            this.$store.dispatch('tableDiningOrder/show', this.$route.query.id).then(res => {
                const modalTarget = this.$refs.confirmOrder;
                modalTarget?.classList?.add("active");
                document.body.style.overflowY = "hidden";
                this.loading.isActive = false;
            }).catch(() => {
                this.loading.isActive = false;
            });
        }
    },
    watch: {
        whatsappNumber(newVal) {
            this.$store.dispatch('tableCart/setWhatsapp', newVal).then().catch();
        }
    },
    methods: {
        onBranchChange() {
            if (this.selectedBranch) {
                // Initialize fake "table" with branch info for cart system
                this.$store.dispatch('tableCart/initTable', {
                    id: 0, // No table for online orders
                    branch_id: this.selectedBranch,
                    name: 'Online Order',
                    slug: 'online'
                }).then().catch();

                this.itemList();
            }
        },
        closeModal: function () {
            const modalTarget = this.$refs.confirmOrder;
            modalTarget?.classList?.remove("active");
            document.body.style.overflowY = "auto";
            this.loading.isActive = false;
        },
        allCategory: function (category) {
            this.itemProps.search.item_category_id = "";
            this.category = {
                id: 0,
                name: category.name
            }
            this.itemList();
        },
        setCategory: function (id, slug = null) {
            this.itemProps.search.item_category_id = id;
            this.itemList();
            if (slug !== null) {
                this.loading.isActive = true;
                this.$store.dispatch("tableItemCategory/show", {
                    slug: slug
                }).then((res) => {
                    this.category = res.data.data;
                    this.loading.isActive = false;
                }).catch(() => {
                    this.loading.isActive = false;
                });
            }
        },
        itemList: function () {
            if (!this.selectedBranch) return;

            this.loading.isActive = true;
            this.itemProps.search.branch_id = this.selectedBranch;
            
            this.$store.dispatch("frontendItem/lists", this.itemProps.search).then(() => {
                this.loading.isActive = false;
            }).catch(() => {
                this.loading.isActive = false;
            });
        },
        itemTypeSet: function (e) {
            this.itemProps.property.type = e;
        },
        itemTypeReset: function () {
            this.itemProps.property.type = null;
        },
    }
}

</script>

