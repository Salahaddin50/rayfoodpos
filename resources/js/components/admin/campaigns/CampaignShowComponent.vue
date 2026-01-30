<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 mb-4 sm:mb-0">
            <button type="button" @click="handleTab($event, '#information', '.db-tabBtn', '.db-tabDiv', 'active')"
                class="db-tabBtn !justify-start active">
                <i class="lab lab-information lab-font-size-16"></i>
                {{ $t('label.information') }}
            </button>
            <button type="button" @click="handleTab($event, '#registrations', '.db-tabBtn', '.db-tabDiv', 'active')"
                class="db-tabBtn !justify-start">
                <i class="lab lab-users lab-font-size-16"></i>
                Registrations
            </button>
        </div>
        <div class="db-tabDiv active" id="information">
            <div class="row py-2">
                <div class="col-12 sm:col-6 !py-1.5">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.name') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ campaign.name }}</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.type') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ campaign.type_name }}</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5" v-if="campaign.type === campaignTypeEnum.PERCENTAGE">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.discount_percentage') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ campaign.flat_discount_value }}%</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5" v-if="campaign.type === campaignTypeEnum.ITEM">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">Required Purchases</span>
                        <span class="db-list-item-text w-full sm:w-1/2">Buy {{ campaign.required_purchases }} Get 1 Free</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5" v-if="campaign.description">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.description') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ campaign.description }}</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5" v-if="campaign.start_date">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.start_date') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ campaign.convert_start_date }}</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5" v-if="campaign.end_date">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.end_date') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ campaign.convert_end_date }}</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.status') }}</span>
                        <span class="db-list-item-text">
                            <span :class="statusClass(campaign.status)">{{
                                enums.statusEnumArray[campaign.status]
                            }}</span>
                        </span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5" v-if="campaign.registrations_count !== undefined">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">Total Registrations</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ campaign.registrations_count }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="db-tabDiv" id="registrations">
            <div class="row py-2">
                <div class="col-12">
                    <p class="text-sm text-gray-600 mb-4">Campaign registrations will be displayed here.</p>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import statusEnum from "../../../enums/modules/statusEnum";

const campaignTypeEnum = {
    PERCENTAGE: 5,
    ITEM: 10
};

export default {
    name: "CampaignShowComponent",
    components: {
        LoadingComponent,
    },
    data() {
        return {
            campaignTypeEnum: campaignTypeEnum,
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
        };
    },
    mounted() {
        this.show();
    },
    computed: {
        campaign: function () {
            return this.$store.getters["campaign/show"];
        },
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        handleTab: function (event, id, btnClass, divClass, activeClass) {
            return appService.handleTab(event, id, btnClass, divClass, activeClass);
        },
        show: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch("campaign/show", this.$route.params.id)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
    },
};
</script>
