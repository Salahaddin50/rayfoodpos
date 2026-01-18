<template>
    <LoadingComponent :props="loading" />
    <div class="col-12 xl:col-6">
        <div class="db-card">
            <div class="db-card-header">
                <div class="db-card-title">{{ $t('label.featured_items') }}</div>
            </div>
            <div class="db-card-body">
                <div
                    class="grid gap-3 sm:gap-[18px] grid-cols-[repeat(auto-fill,_minmax(140px,_1fr))] sm:grid-cols-[repeat(auto-fill,_minmax(185px,_1fr))]">
                    <div v-for="item in featured_items" :key="item"
                        class="pos-card-height rounded-2xl border transition border-[#EFF0F6] bg-white hover:shadow-xs"
                        :class="isPassive(item) ? 'opacity-60' : ''">
                        <img class="pos-image-height w-full rounded-t-2xl" :src="item.thumb" alt="item">
                        <div class="py-3 px-3 rounded-b-2xl">
                            <h3
                                class="text-sm mb-3 font-medium font-rubik capitalize text-ellipsis whitespace-nowrap overflow-hidden">
                                {{ textShortener(item.name, 25) }}</h3>
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="font-rubik">{{ item.offer.length > 0 ? item.offer[0].currency_price : item.currency_price
                                }}</h4>
                                <button :disabled="isPassive(item)" @click.prevent="variationModalShow(item)" data-modal="#item-variation-modal"
                                    class="db-product-cart pos-add-button flex items-center gap-1.5 rounded-3xl capitalize text-sm font-medium font-rubik py-1 px-2 shadow-cardCart transition bg-white hover:bg-primary">
                                    <i class="lab lab-bag-2 font-fill-primary transition lab-font-size-14"></i>
                                    <span class="text-xs font-rubik text-primary transition">
                                        {{ isPassive(item) ? $t('label.inactive') : $t('button.add') }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import appService from "../../../services/appService";
import statusEnum from "../../../enums/modules/statusEnum";
export default {
    name: "FeaturedItemsComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
            },
            featured_items: {},
        };
    },
    mounted() {
        this.featuredItems();
    },
    methods: {
        isPassive(item) {
            return item && typeof item.effective_status !== "undefined" && item.effective_status !== this.enums.statusEnum.ACTIVE;
        },
        textShortener: function (text, number) {
            return appService.textShortener(text, number);
        },
        variationModalShow: function (item) {
            // This could be implemented to open a modal or navigate to item details
            // For now, keeping it simple as this is a dashboard view component
        },
        featuredItems: function () {
            this.loading.isActive = true;
            this.$store.dispatch('dashboard/featuredItems').then(res => {
                this.featured_items = res.data.data;
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
    },
}
</script>
