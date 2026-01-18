<template>
  <LoadingComponent :props="loading" />
  <div class="col-span-2 md:block hidden">
    <div class="customer-screen db-card rounded-[10px] h-screen md:h-[calc(100vh-117px)] overflow-hidden pb-20">
      <div class="p-3 pb-2 mb-6">
        <h3 class="text-[22px] font-semibold text-[#0084FF]">{{ $t("label.popular_menu_items") }}</h3>
      </div>
      <div class="p-3 overflow-auto thin-scrolling h-full">
        <div
          class="grid gap-3 sm:gap-[18px] grid-cols-[repeat(auto-fill,_minmax(140px,_1fr))] sm:grid-cols-[repeat(auto-fill,_minmax(185px,_1fr))] mb-8 md:mb-0">
          <div v-for="item in items" :key="item"
            class="pos-card-height rounded-2xl border transition border-[#EFF0F6] bg-white hover:shadow-xs">
            <img class="pos-image-height w-full rounded-t-2xl" :src="item.thumb" alt="item">
            <div class="py-3 px-3 rounded-b-2xl">
              <h3
                class="text-sm mb-3 font-medium font-rubik capitalize text-ellipsis whitespace-nowrap overflow-hidden">
                {{ textShortener(item.name, 25) }}</h3>
              <div class="flex items-center justify-between gap-2">
                <h4 class="font-rubik">{{ item.offer && item.offer.length > 0 ? item.offer[0].currency_price : item.currency_price }}</h4>
                <button data-modal="#item-variation-modal"
                  class="db-product-cart pos-add-button flex items-center gap-1.5 rounded-3xl capitalize text-sm font-medium font-rubik py-1 px-2 shadow-cardCart transition bg-white hover:bg-primary">
                  <i class="lab lab-bag-2 font-fill-primary transition lab-font-size-14"></i>
                  <span class="text-xs font-rubik text-primary transition">{{ $t('button.add') }}</span>
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

export default {
  name: "PopularItemComponent",
  components: {
    LoadingComponent,
  },
  data() {
    return {
      loading: {
        isActive: false,
      },
    };
  },
  computed: {
    items: function () {
      return this.$store.getters["orderStatusScreenOrder/mostPopularItems"];
    },
  },
  mounted() {
    this.popularItems();
  },
  methods: {
    textShortener: function (text, number) {
      return appService.textShortener(text, number);
    },
    popularItems: function () {
      this.loading.isActive = true;
      this.$store
        .dispatch("orderStatusScreenOrder/mostPopularItems")
        .then((res) => {

          this.loading.isActive = false;
        })
        .catch((err) => {
          this.loading.isActive = false;
        });
    },
  },
};
</script>