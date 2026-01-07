<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">

        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">Takeaway Types</h3>

                <div class="db-card-filter">
                    <button v-print="printObj" class="db-btn h-[37px] text-white bg-primary">
                        <i class="lab lab-printer-line lab-font-size-17"></i>
                        {{ $t('button.print') }}
                    </button>
                </div>
            </div>
            <div class="db-card-body" id="print">
                <img class="w-36 mx-auto mb-1" :src="setting.theme_logo" alt="logo">
                <p class="text-center">
                    <span class="block capitalize mt-4">Takeaway Type</span>
                    <span class="block capitalize mb-6">{{ takeawayType.branch?.name }}</span>
                </p>

                <p class="text-center mb-6">
                    <span class="block capitalize font-medium">{{ takeawayType.name }}</span>
                    <span class="block capitalize text-sm mt-1">
                        Sort Order: {{ takeawayType.sort_order }}
                    </span>
                    <span class="block capitalize text-sm mt-1">
                        Status: {{ enums.statusEnumArray[takeawayType.status] }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import print from "vue3-print-nb";
import statusEnum from "../../../enums/modules/statusEnum";
import appService from "../../../services/appService";

export default {
    name: "TakeawayTypeShowComponent",
    components: { LoadingComponent, PrintComponent },
    directives: { print },
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
            printObj: {
                id: "print",
                popTitle: "Takeaway Types",
            },
        };
    },
    computed: {
        takeawayType: function () {
            return this.$store.getters["takeawayType/show"];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch("takeawayType/show", this.$route.params.id).then((res) => {
            this.loading.isActive = false;
        }).catch((err) => {
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

