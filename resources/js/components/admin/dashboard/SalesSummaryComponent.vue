<template>
    <LoadingComponent :props="loading" />
    <div class="col-12 xl:col-6">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('label.sales_summary') }}</h3>
                <div id="sales-range" class="cursor-pointer flex items-center gap-3 custom-datepicker">
                    <Datepicker hideInputIcon autoApply :enableTimePicker="false" utc="false"
                        @update:modelValue="salesSummary" v-model="date" range :preset-ranges="presetRanges">
                        <template #yearly="{ label, range, presetDateRange }">
                            <span @click="presetDateRange(range)">{{ label }}</span>
                        </template>
                    </Datepicker>
                    <i class="lab lab-calendar lab-font-size-24 lab-color-pink"></i>
                </div>
            </div>
            <div class="db-card-body">
                <ul class="flex gap-11">
                    <li>
                        <div class="flex items-center gap-2.5">
                            <i class="lab lab-sale-summary lab-font-size-20 lab-font-color-2"></i>
                            <h3 class="font-bold text-[22px] leading-[34px]">{{ total_sales }}</h3>
                        </div>
                        <p class="text-xs capitalize">{{ $t("label.total_sales") }}</p>
                    </li>
                    <li>
                        <div class="flex items-center gap-2.5">
                            <i class="lab lab-sale-summary lab-font-size-20 lab-font-color-2"></i>
                            <h3 class="font-bold text-[22px] leading-[34px]">{{ avg_per_day }}</h3>
                        </div>
                        <p class="text-xs capitalize">{{ $t("label.avg_sales_per_day") }}</p>
                    </li>
                </ul>

                <div id="area-chart"></div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { endOfMonth, startOfMonth, subMonths } from 'date-fns';

export default {
    name: "SalesSummaryComponent",
    components: { LoadingComponent, Datepicker },
    data() {
        return {
            loading: {
                isActive: false,
            },
            date: null,
            first_date: null,
            last_date: null,
            total_sales: null,
            avg_per_day: null,
            presetRanges: [
                { label: 'Today', range: [new Date(), new Date()] },
                { label: 'This month', range: [startOfMonth(new Date()), endOfMonth(new Date())] },
                {
                    label: 'Last month',
                    range: [startOfMonth(subMonths(new Date(), 1)), endOfMonth(subMonths(new Date(), 1))],
                },
            ]
        };
    },
    mounted() {
        const date = new Date();
        const startDate = new Date(date.getFullYear(), date.getMonth(), 1);
        const endDate = new Date(date.getFullYear(), date.getMonth() + 1, 0);
        this.date = [startDate, endDate];
        this.salesSummary();
    },
    methods: {
        salesSummary: function (e) {
            let date = {
                first_date: '',
                last_date: '',
            };
            if (e) {
                this.first_date = e[0];
                this.last_date = e[1];
                date.first_date = e[0];
                date.last_date = e[1];
            }

            this.loading.isActive = true;
            this.$store.dispatch("dashboard/salesSummary", date).then((res) => {
                this.total_sales = res.data.data.total_sales;
                this.avg_per_day = res.data.data.avg_per_day;
                
                // Generate day labels (1, 2, 3, ... for the number of days)
                const dayLabels = res.data.data.per_day_sales.map((_, index) => (index + 1).toString());
                
                let options = {
                    series: [{
                        name: this.$t('label.sales'),
                        data: res.data.data.per_day_sales,
                    }],
                    chart: {
                        type: 'bar',
                        height: 250,
                        fontFamily: 'inherit',
                        parentHeightOffset: 0,
                        zoom: { enabled: false },
                        toolbar: { show: false, },
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '50%',
                        }
                    },
                    xaxis: {
                        categories: dayLabels,
                        labels: {
                            show: true,
                            style: {
                                colors: '#6B7280',
                                fontSize: '12px',
                            }
                        },
                        axisBorder: { 
                            show: true,
                            color: '#E5E7EB'
                        },
                        axisTicks: {
                            show: true,
                            color: '#E5E7EB'
                        }
                    },
                    yaxis: { 
                        show: true,
                        labels: {
                            show: true,
                            style: {
                                colors: '#6B7280',
                                fontSize: '12px',
                            },
                            formatter: function(value) {
                                return '₼' + value.toFixed(0);
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return '₼' + value.toFixed(2);
                            }
                        }
                    },
                    stroke: {
                        width: 0,
                    },
                    colors: ["#FF4F99"],
                    grid: { 
                        show: true,
                        borderColor: '#F3F4F6',
                        strokeDashArray: 3,
                    },
                    dataLabels: { enabled: false, },
                };

                let chart = new ApexCharts(document.querySelector("#area-chart"), options);
                chart.render();
                if (date.first_date !== '' && date.last_date !== '') {
                    chart.updateSeries([{ data: res.data.data.per_day_sales }]);
                }
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
    }
}
</script>