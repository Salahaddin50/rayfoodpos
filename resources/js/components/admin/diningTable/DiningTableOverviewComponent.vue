<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.table_overview') }}</h3>
                <div class="db-card-filter">
                    <button @click="refresh" class="db-btn py-2 text-white bg-primary">
                        <i class="lab lab-refresh lab-font-size-16"></i>
                        <span>{{ $t('button.refresh') }}</span>
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <!-- Tables Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4" v-if="tables.length > 0">
                    <div 
                        v-for="table in tables" 
                        :key="table.id"
                        class="table-card"
                        :class="{ 'has-orders': table.has_orders, 'empty': !table.has_orders }"
                    >
                        <!-- Table Header -->
                        <div class="table-header">
                            <span class="table-number">{{ table.name }}</span>
                            <span v-if="table.size" class="table-size">
                                <i class="lab lab-user lab-font-size-12"></i> {{ table.size }}
                            </span>
                        </div>

                        <!-- Orders List -->
                        <div class="table-orders" v-if="table.has_orders">
                            <div 
                                v-for="order in table.orders" 
                                :key="order.id"
                                class="order-item"
                                @click="viewOrder(order)"
                            >
                                <div class="order-info">
                                    <span class="order-number">#{{ order.order_serial_no }}</span>
                                    <span class="order-time">{{ order.created_at }}</span>
                                </div>
                                <div class="order-total">{{ order.total_formatted }}</div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div class="table-empty" v-else>
                            <span class="empty-text">{{ $t('label.available') }}</span>
                        </div>

                        <!-- Table Footer -->
                        <div class="table-footer" v-if="table.has_orders">
                            <span class="order-count">{{ table.order_count }} {{ $t('label.orders') }}</span>
                        </div>
                    </div>
                </div>

                <!-- No Tables Message -->
                <div v-else class="text-center py-12">
                    <div class="max-w-[200px] mx-auto mb-4">
                        <img class="w-full h-full opacity-50" :src="ENV.API_URL + '/images/default/not-found.png'" alt="No tables">
                    </div>
                    <p class="text-gray-500">{{ $t('message.no_data_available') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import ENV from "../../../config/env";
import sourceEnum from "../../../enums/modules/sourceEnum";

export default {
    name: "DiningTableOverviewComponent",
    components: {
        LoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            ENV: ENV,
            refreshInterval: null
        }
    },
    computed: {
        tables: function () {
            return this.$store.getters['diningTable/overview'];
        }
    },
    mounted() {
        this.loadTables();
        // Auto-refresh every 30 seconds
        this.refreshInterval = setInterval(() => {
            this.loadTables(false);
        }, 30000);
    },
    beforeUnmount() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    },
    methods: {
        loadTables: function (showLoading = true) {
            if (showLoading) {
                this.loading.isActive = true;
            }
            this.$store.dispatch('diningTable/overview').then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        refresh: function () {
            this.loadTables();
        },
        viewOrder: function (order) {
            // Navigate based on order source
            // Source.POS = 15, others are table/web orders
            if (order.source === sourceEnum.POS) {
                this.$router.push({ name: 'admin.pos.orders.show', params: { id: order.id } });
            } else {
                this.$router.push({ name: 'admin.table.orders.show', params: { id: order.id } });
            }
        }
    }
}
</script>

<style scoped>
.table-card {
    display: flex;
    flex-direction: column;
    min-height: 140px;
    border-radius: 12px;
    border: 2px solid #e5e7eb;
    background: #fff;
    overflow: hidden;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.table-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.table-card.has-orders {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
}

.table-card.empty {
    border-color: #10b981;
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    background: rgba(0, 0, 0, 0.03);
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.table-number {
    font-weight: 700;
    font-size: 14px;
    color: #1f2937;
}

.table-size {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #6b7280;
    background: rgba(0, 0, 0, 0.05);
    padding: 2px 8px;
    border-radius: 12px;
}

.table-orders {
    flex: 1;
    padding: 8px;
    overflow-y: auto;
    max-height: 120px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 8px;
    margin-bottom: 4px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.order-item:hover {
    background: #fff;
    border-color: #3b82f6;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
}

.order-item:last-child {
    margin-bottom: 0;
}

.order-info {
    display: flex;
    flex-direction: column;
}

.order-number {
    font-weight: 600;
    font-size: 12px;
    color: #3b82f6;
}

.order-time {
    font-size: 10px;
    color: #9ca3af;
}

.order-total {
    font-weight: 600;
    font-size: 11px;
    color: #059669;
}

.table-empty {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.empty-text {
    font-size: 13px;
    font-weight: 500;
    color: #10b981;
}

.table-footer {
    padding: 6px 12px;
    background: rgba(0, 0, 0, 0.03);
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    text-align: center;
}

.order-count {
    font-size: 11px;
    font-weight: 500;
    color: #6b7280;
}

/* Custom scrollbar for orders */
.table-orders::-webkit-scrollbar {
    width: 4px;
}

.table-orders::-webkit-scrollbar-track {
    background: transparent;
}

.table-orders::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 4px;
}

.table-orders::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.2);
}
</style>
