<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title flex items-center gap-2">
                    <i class="lab lab-customers lab-font-size-20"></i>
                    <span>{{ $t("menu.online_users") }}</span>
                </h3>
                <div class="db-card-filter">
                    <div class="flex items-center gap-3">
                        <div class="text-xs text-gray-500" v-if="branch && branch.name">
                            {{ $t("label.branch") }}: <b class="font-medium">{{ branch.name }}</b>
                        </div>
                        <div class="dropdown-group">
                            <ExportComponent />
                            <div class="dropdown-list db-card-filter-dropdown-list transition-all duration-300 scale-y-0 origin-top">
                                <ExcelComponent :method="xls" />
                            </div>
                        </div>
                        <div class="dropdown-group">
                            <ImportComponent />
                            <div class="dropdown-list db-card-filter-dropdown-list transition-all duration-300 scale-y-0 origin-top">
                                <SampleFileComponent @click="downloadSample" />
                                <UploadFileComponent :dataModal="'onlineUserUpload'" @click="uploadModal('#onlineUserUpload')" />
                            </div>
                        </div>
                        <button class="db-btn py-2 text-white bg-primary" @click.prevent="list()">
                            <i class="lab lab-refresh-line lab-font-size-16"></i>
                            <span>{{ $t("button.refresh") }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="db-table-responsive">
                <table class="db-table stripe">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t("label.whatsapp_number") }}</th>
                            <th class="db-table-head-th">{{ $t("label.location") }}</th>
                            <th class="db-table-head-th">{{ $t("label.last_order") }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="rows.length > 0">
                        <tr class="db-table-body-tr" v-for="row in uniqueRows" :key="row.id">
                            <td class="db-table-body-td">
                                <span dir="ltr">{{ row.whatsapp }}</span>
                            </td>
                            <td class="db-table-body-td">
                                {{ row.location }}
                            </td>
                            <td class="db-table-body-td">
                                {{ row.last_order_at }}
                            </td>
                        </tr>
                    </tbody>
                    <tbody class="db-table-body" v-else>
                        <tr class="db-table-body-tr">
                            <td class="db-table-body-td text-center" colspan="3">
                                <div class="p-4">
                                    {{ $t("message.no_data_available") }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <OnlineUserUploadComponent v-on:list="list" />
</template>

<script>
import LoadingComponent from "../components/LoadingComponent.vue";
import ExportComponent from "../components/buttons/export/ExportComponent.vue";
import ExcelComponent from "../components/buttons/export/ExcelComponent.vue";
import ImportComponent from "../components/buttons/import/ImportComponent.vue";
import SampleFileComponent from "../components/buttons/import/SampleFileComponent.vue";
import UploadFileComponent from "../components/buttons/import/UploadFileComponent.vue";
import OnlineUserUploadComponent from "./OnlineUserUploadComponent.vue";
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";

export default {
    name: "OnlineUserListComponent",
    components: {
        LoadingComponent,
        ExportComponent,
        ExcelComponent,
        ImportComponent,
        SampleFileComponent,
        UploadFileComponent,
        OnlineUserUploadComponent,
    },
    data() {
        return {
            loading: { isActive: false },
            rows: [],
        };
    },
    computed: {
        branch() {
            return this.$store.getters["backendGlobalState/branchShow"];
        },
        onlineUsers() {
            return this.$store.getters["onlineUser/lists"];
        },
        uniqueRows() {
            const map = new Map();
            const normalize = (v) => String(v ?? "").trim().replace(/\s+/g, "");
            (this.rows || []).forEach((r) => {
                const key = normalize(r?.whatsapp);
                if (!key) return;
                if (!map.has(key)) map.set(key, r);
            });
            return Array.from(map.values());
        },
    },
    mounted() {
        this.list();
    },
    methods: {
        list() {
            this.loading.isActive = true;
            this.$store.dispatch("onlineUser/lists", { paginate: 0, order_column: "last_order_at", order_type: "desc" })
                .then(() => {
                    this.rows = this.onlineUsers || [];
                    this.loading.isActive = false;
                })
                .catch(() => {
                    this.loading.isActive = false;
                });
        },
        uploadModal(id) {
            appService.modalShow(id);
        },
        xls() {
            this.loading.isActive = true;
            this.$store.dispatch("onlineUser/export", { paginate: 0 }).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.online_users");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err?.response?.data?.message || "Export failed");
            });
        },
        downloadSample() {
            this.loading.isActive = true;
            this.$store.dispatch("onlineUser/downloadSample").then((res) => {
                this.loading.isActive = false;
                const url = window.URL.createObjectURL(new Blob([res.data]));
                const link = document.createElement("a");
                link.href = url;
                link.download = "Online Users Import Sample.xlsx";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch(() => {
                this.loading.isActive = false;
            });
        },
    },
};
</script>


