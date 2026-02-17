<template>
  <LoadingContentComponent :props="loading" />
  <div class="col-span-1 customer-screen db-card rounded-[10px] h-screen md:h-[calc(100vh-117px)] overflow-hidden">
    <h3 class="text-lg font-semibold text-white p-3 pb-2 bg-primary mb-2 rounded-t-[10px] text-center relative">
      {{ $t("label.preparing") }}
      <button
        @click="refreshPage"
        type="button"
        class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center bg-white/20 hover:bg-white/30 transition-colors"
        :title="$t('button.refresh')">
        <i class="fa-solid fa-rotate-right text-white text-sm opacity-100"></i>
      </button>
    </h3>
    <div class="content-wrapper p-3 overflow-auto thin-scrolling h-full">
      <ul class="w-full text-center text-[#1F1F39] mb-20">
        <li v-for="preparingItem in preparingItems" :key="preparingItem.id" class="mb-6">
          <div class="text-[40px] font-semibold leading-10">
            <template v-if="preparingItem.token && preparingItem.token !== 'online' && preparingItem.token !== '' && preparingItem.token !== null">{{ preparingItem.token }}</template>
            <template v-else>{{ $t('label.online') }}</template>
          </div>
          <div v-if="ossDetails(preparingItem)" class="text-sm font-medium text-[#6E7191] capitalize">
            {{ ossDetails(preparingItem) }}
          </div>
        </li>
      </ul>
    </div>
  </div>
  <div class="col-span-1 customer-screen db-card rounded-[10px] h-screen md:h-[calc(100vh-117px)] overflow-hidden">
    <h3 class="text-lg font-semibold text-white p-3 pb-2 bg-[#1AB759] mb-2 rounded-t-[10px] text-center relative">
      {{ $t("label.ready") }}
      <button
        @click="refreshPage"
        type="button"
        class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center bg-white/20 hover:bg-white/30 transition-colors"
        :title="$t('button.refresh')">
        <i class="fa-solid fa-rotate-right text-white text-sm opacity-100"></i>
      </button>
    </h3>
    <div class="content-wrapper p-3 overflow-auto thin-scrolling h-full">
      <ul class="w-full text-center text-[#1F1F39] mb-20">
        <li v-for="preparedItem in preparedItems" :key="preparedItem.id" class="mb-6">
          <div class="text-[40px] font-semibold leading-10">
            <template v-if="preparedItem.token && preparedItem.token !== 'online' && preparedItem.token !== '' && preparedItem.token !== null">{{ preparedItem.token }}</template>
            <template v-else>{{ $t('label.online') }}</template>
          </div>
          <div v-if="ossDetails(preparedItem)" class="text-sm font-medium text-[#6E7191] capitalize">
            {{ ossDetails(preparedItem) }}
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>
<script>
import LoadingContentComponent from "../components/LoadingContentComponent";
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";


export default {
  name: "PreparingAndReadyComponent",
  components: {
    LoadingContentComponent,
  },
  data() {
    return {
      loading: {
        isActive: false,
      },
      preparedItems: [],
      preparingItems: [],
      enums: {
        orderStatusEnum: orderStatusEnum,
        orderTypeEnum: orderTypeEnum,
      },
      autoRefreshInterval: null,
      previousOrderIds: [],
      previousDeliveredOrderIds: [],
      audioElement: null,
    };
  },
  computed: {
    orders: function () {
      return this.$store.getters["orderStatusScreenOrder/lists"];
    },
    items: function () {
      return this.$store.getters["orderStatusScreenOrder/mostPopularItems"];
    },
    setting: function () {
      return this.$store.getters['frontendSetting/lists'];
    },
  },
  mounted() {
    this.list();
    this.startAutoRefresh();
  },
  methods: {
    ossDetails: function (item) {
      // Check if it's an online order (has whatsapp_number)
      if (item.whatsapp_number) {
        return this.$t('label.online_order');
      }
      if (item.order_type === orderTypeEnum.TAKEAWAY) {
        return item.takeaway_type_name ? `Takeaway/${item.takeaway_type_name}` : "";
      }
      if (item.order_type === orderTypeEnum.DINING_TABLE) {
        let result = item.table_name ? `Table/${item.table_name}` : "";
        // Add token number if assigned (token can be null, empty, or a string)
        if (item.token && item.token !== 'online' && item.token !== '' && item.token !== null) {
          result += result ? ` / Token: ${item.token}` : `Token: ${item.token}`;
        }
        return result;
      }
      return "";
    },
    startAutoRefresh() {
      if (this.$route.path.includes('order-status-screen')) {
        this.autoRefreshInterval = setInterval(() => {
          this.list();
        }, 60000); // 60 seconds
      }
    },
    stopAutoRefresh() {
      if (this.autoRefreshInterval) {
        clearInterval(this.autoRefreshInterval);
        this.autoRefreshInterval = null;
      }
    },
    list: function () {
      this.loading.isActive = true;
      this.$store
        .dispatch("orderStatusScreenOrder/lists")
        .then((res) => {
          const allOrders = res.data.data || [];
          
          console.log('OSS Response - Total orders:', allOrders.length);
          console.log('OSS Response - Order statuses:', allOrders.map(o => ({ id: o.id, serial: o.order_serial_no, status: o.status, token: o.token, table: o.table_name })));
          
          // Count DELIVERED orders
          const deliveredOrders = allOrders.filter(order => order.status === orderStatusEnum.DELIVERED);
          console.log('OSS DELIVERED orders in response:', deliveredOrders.length);
          
          this.preparingItems = allOrders.filter(
            (item) => item.status === orderStatusEnum.PREPARING
          );
          this.preparedItems = allOrders.filter(
            (item) => item.status === orderStatusEnum.PREPARED
          );

          this.loading.isActive = false;
          
          // Initialize previousOrderIds if not already set (first load)
          if (this.previousOrderIds.length === 0 && allOrders.length > 0) {
            this.previousOrderIds = allOrders.map(order => order.id);
            // Get DELIVERED order IDs on first load
            this.previousDeliveredOrderIds = deliveredOrders.map(order => order.id);
            console.log('OSS First load - initialized with', this.previousOrderIds.length, 'orders,', this.previousDeliveredOrderIds.length, 'with DELIVERED status');
          } else {
            // Check for new orders and play sound (only after first load)
            this.checkForNewOrders(allOrders);
          }
        })
        .catch((err) => {
          this.loading.isActive = false;
        });
    },
    checkForNewOrders: function (allOrders = null) {
      // Use provided orders or fall back to computed property
      const ordersToCheck = allOrders || this.orders || [];
      
      if (!ordersToCheck || ordersToCheck.length === 0) {
        // If no orders, reset tracking
        this.previousOrderIds = [];
        this.previousDeliveredOrderIds = [];
        return;
      }

      // Get current order IDs
      const currentOrderIds = ordersToCheck.map(order => order.id);
      
      // Get current orders with DELIVERED status
      const deliveredOrders = ordersToCheck.filter(order => order.status === orderStatusEnum.DELIVERED);
      const currentDeliveredOrderIds = deliveredOrders.map(order => order.id);
      
      console.log('OSS Current DELIVERED order IDs:', currentDeliveredOrderIds);
      console.log('OSS Previous DELIVERED order IDs:', this.previousDeliveredOrderIds);
      console.log('OSS All orders statuses:', ordersToCheck.map(o => ({ id: o.id, serial: o.order_serial_no, status: o.status })));
      
      // Check if we have previous orders to compare
      if (this.previousOrderIds.length > 0) {
        // Find new orders (orders that weren't in previous list)
        const newOrderIds = currentOrderIds.filter(id => !this.previousOrderIds.includes(id));
        
        // Find orders that changed TO DELIVERED status (were not DELIVERED before, but are now)
        const newDeliveredOrderIds = currentDeliveredOrderIds.filter(id => 
          !this.previousDeliveredOrderIds.includes(id)
        );
        
        console.log('OSS New order IDs found:', newOrderIds);
        console.log('OSS New DELIVERED order IDs (status changed):', newDeliveredOrderIds);
        
        // Play sound if:
        // 1. New orders with DELIVERED status appeared, OR
        // 2. Existing orders changed status TO DELIVERED
        if (newDeliveredOrderIds.length > 0) {
          console.log('OSS New DELIVERED orders detected (new orders or status changed) - playing sound');
          this.playRingingSound();
        } else if (newOrderIds.length > 0) {
          // Check if any of the new orders have DELIVERED status
          const newOrdersWithDelivered = ordersToCheck.filter(order => 
            newOrderIds.includes(order.id) && 
            order.status === orderStatusEnum.DELIVERED
          );
          
          if (newOrdersWithDelivered.length > 0) {
            console.log('OSS New orders with DELIVERED status detected:', newOrdersWithDelivered.length);
            this.playRingingSound();
          } else {
            console.log('OSS New orders found but none have DELIVERED status');
          }
        } else {
          console.log('OSS No new DELIVERED orders detected');
        }
      } else {
        // First load - initialize tracking
        console.log('OSS First load - initializing order tracking');
      }
      
      // Update previous order IDs and DELIVERED order IDs for next comparison
      this.previousOrderIds = [...currentOrderIds];
      this.previousDeliveredOrderIds = [...currentDeliveredOrderIds];
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
    refreshPage: function () {
      // Refresh OSS data without reloading the page
      this.list();
    },
  },
  beforeUnmount() {
    this.stopAutoRefresh();
    
    // Clean up audio element
    if (this.audioElement) {
      this.audioElement.pause();
      this.audioElement = null;
    }
  },
};
</script>