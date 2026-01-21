<template>
    <LoadingComponent :props="loading" />

    <div class="db-card">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ $t("label.branch_id") }}</h3>
        </div>
        <div class="db-card-body">
            <div class="row">
                <div class="col-12">
                    <ul class="db-list single">
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.name")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.name
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.latitude")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.latitude
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.longitude")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.longitude
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.email")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.email
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.phone")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.phone
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.city")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.city
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.state")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.state
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.zip_code")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.zip_code
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.address")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.address
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.status")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.status)" class="db-badge">{{
                                    enums.statusEnumArray[branch.status]
                                }}</span>
                            </span>
                        </li>

                        <li class="db-list-item">
                            <span class="db-list-item-title">{{ $t("label.free_delivery_threshold") }}</span>
                            <span class="db-list-item-text">{{ branch.free_delivery_threshold ?? '-' }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{ $t("label.delivery_distance_threshold_1") }} (km)</span>
                            <span class="db-list-item-text">{{ branch.delivery_distance_threshold_1 ?? '-' }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{ $t("label.delivery_distance_threshold_2") }} (km)</span>
                            <span class="db-list-item-text">{{ branch.delivery_distance_threshold_2 ?? '-' }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{ $t("label.delivery_cost_1") }}</span>
                            <span class="db-list-item-text">{{ branch.delivery_cost_1 ?? '-' }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{ $t("label.delivery_cost_2") }}</span>
                            <span class="db-list-item-text">{{ branch.delivery_cost_2 ?? '-' }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{ $t("label.delivery_cost_3") }}</span>
                            <span class="db-list-item-text">{{ branch.delivery_cost_3 ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../../components/LoadingComponent";
import statusEnum from "../../../../enums/modules/statusEnum";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";

export default {
    name: "BranchShowComponent",
    components: {
        LoadingComponent,
    },
    data() {
        return {
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
    computed: {
        branch: function () {
            return this.$store.getters["branch/show"];
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store
            .dispatch("branch/show", this.$route.params.id)
            .then((res) => {
                this.loading.isActive = false;
            })
            .catch((error) => {
                this.loading.isActive = false;
            });
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
    },
};
</script>
