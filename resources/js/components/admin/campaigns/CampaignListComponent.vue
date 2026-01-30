<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t("menu.campaigns") }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent @click.prevent="handleSlide('campaign-filter')" />
                    <CampaignCreateComponent :props="props" v-if="permissionChecker('campaigns_create')" />
                </div>
            </div>
            <div class="table-filter-div" id="campaign-filter">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchName" class="db-field-title after:hidden">{{
                                $t("label.name")
                                }}</label>
                            <input id="searchName" v-model="props.search.name" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchType" class="db-field-title after:hidden">{{
                                $t("label.type")
                                }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchType"
                                v-model="props.search.type" :options="[
                                    { id: campaignTypeEnum.PERCENTAGE, name: 'Percentage' },
                                    { id: campaignTypeEnum.ITEM, name: 'Item' },
                                ]" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">{{
                                $t("label.status")
                                }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.status" :options="[
                                    { id: enums.statusEnum.ACTIVE, name: $t('label.active') },
                                    { id: enums.statusEnum.INACTIVE, name: $t('label.inactive') },
                                ]" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />
                        </div>
                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t("button.search") }}</span>
                                </button>
                                <button class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                    <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                    <span>{{ $t("button.clear") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="db-table-responsive">
                <table class="db-table stripe" id="print" :dir="direction">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t("label.name") }}</th>
                            <th class="db-table-head-th">{{ $t("label.type") }}</th>
                            <th class="db-table-head-th">{{ $t("label.discount_value") }}</th>
                            <th class="db-table-head-th">{{ $t("label.start_date") }}</th>
                            <th class="db-table-head-th">{{ $t("label.end_date") }}</th>
                            <th class="db-table-head-th">{{ $t("label.status") }}</th>
                            <th class="db-table-head-th hidden-print"
                                v-if="permissionChecker('campaigns_show') || permissionChecker('campaigns_edit') || permissionChecker('campaigns_delete')">
                                {{ $t("label.action") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="campaigns.length > 0">
                        <tr class="db-table-body-tr" v-for="campaign in campaigns" :key="campaign">
                            <td class="db-table-body-td">
                                <div v-if="campaign.name.length < 40">{{ campaign.name }}</div>
                                <div v-else>{{ campaign.name.substring(0, 40) + ".." }}</div>
                            </td>
                            <td class="db-table-body-td">{{ campaign.type_name }}</td>
                            <td class="db-table-body-td">
                                <span v-if="campaign.type === campaignTypeEnum.PERCENTAGE">{{ campaign.flat_discount_value }}%</span>
                                <span v-else-if="campaign.type === campaignTypeEnum.ITEM">Buy {{ campaign.required_purchases }} Get 1 Free</span>
                                <span v-else>-</span>
                            </td>
                            <td class="db-table-body-td">{{ campaign.convert_start_date || '-' }}</td>
                            <td class="db-table-body-td">{{ campaign.convert_end_date || '-' }}</td>
                            <td class="db-table-body-td">
                                <span :class="statusClass(campaign.status)">
                                    {{ enums.statusEnumArray[campaign.status] }}
                                </span>
                            </td>
                            <td class="db-table-body-td hidden-print"
                                v-if="permissionChecker('campaigns_show') || permissionChecker('campaigns_edit') || permissionChecker('campaigns_delete')">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <SmIconViewComponent :link="'admin.campaign.show'" :id="campaign.id"
                                        v-if="permissionChecker('campaigns_show')" />
                                    <SmIconSidebarModalEditComponent @click="edit(campaign)"
                                        v-if="permissionChecker('campaigns_edit')" />
                                    <SmIconDeleteComponent @click="destroy(campaign.id)"
                                        v-if="permissionChecker('campaigns_delete')" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tbody class="db-table-body" v-else>
                        <tr class="db-table-body-tr">
                            <td class="db-table-body-td text-center" colspan="7">
                                <div class="p-4">
                                    <div class="max-w-[300px] mx-auto mt-2">
                                        <img class="w-full h-full" :src="ENV.API_URL + '/images/default/not-found.png'"
                                            alt="Not Found">
                                    </div>
                                    <span class="d-block mt-3 text-lg">{{ $t('message.no_data_available') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6"
                v-if="campaigns.length > 0">
                <PaginationSMBox :pagination="pagination" :method="list" />
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <PaginationTextComponent :props="{ page: paginationPage }" />
                    <PaginationBox :pagination="pagination" :method="list" />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import CampaignCreateComponent from "./CampaignCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import statusEnum from "../../../enums/modules/statusEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";
import SmIconSidebarModalEditComponent from "../components/buttons/SmIconSidebarModalEditComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import ENV from "../../../config/env";

const campaignTypeEnum = {
    PERCENTAGE: 5,
    ITEM: 10
};

export default {
    name: "CampaignListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        CampaignCreateComponent,
        LoadingComponent,
        SmIconDeleteComponent,
        SmIconSidebarModalEditComponent,
        FilterComponent,
        SmIconViewComponent,
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
            props: {
                form: {
                    name: "",
                    description: "",
                    type: campaignTypeEnum.PERCENTAGE,
                    discount_value: "",
                    free_item_id: null,
                    required_purchases: null,
                    status: statusEnum.ACTIVE,
                    start_date: "",
                    end_date: "",
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: "id",
                    order_type: "desc",
                    name: "",
                    type: null,
                    status: null,
                },
            },
            ENV: ENV
        };
    },
    mounted() {
        this.list();
    },
    computed: {
        campaigns: function () {
            return this.$store.getters["campaign/lists"];
        },
        pagination: function () {
            return this.$store.getters["campaign/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["campaign/page"];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        handleSlide: function (id) {
            return appService.handleSlide(id);
        },
        search: function () {
            this.list();
        },
        clear: function () {
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.name = "";
            this.props.search.type = null;
            this.props.search.status = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch("campaign/lists", this.props.search).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (campaign) {
            appService.sideDrawerShow();
            this.loading.isActive = true;
            this.$store
                .dispatch("campaign/edit", campaign.id)
                .then((res) => {
                    this.loading.isActive = false;
                    this.props.errors = {};
                    this.props.form = {
                        name: campaign.name,
                        description: campaign.description,
                        type: campaign.type,
                        discount_value: campaign.discount_value,
                        free_item_id: campaign.free_item_id,
                        required_purchases: campaign.required_purchases,
                        status: campaign.status,
                        start_date: campaign.start_date,
                        end_date: campaign.end_date,
                    };
                })
                .catch((err) => {
                    alertService.error(err.response.data.message);
                });
        },
        destroy: function (id) {
            appService
                .destroyConfirmation()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch("campaign/destroy", { id: id, search: this.props.search })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(null, this.$t("menu.campaigns"));
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
    },
};
</script>
<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
