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
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="text-xs text-gray-500" v-if="branch && branch.name">
                            {{ $t("label.branch") }}: <b class="font-medium">{{ branch.name }}</b>
                        </div>
                        <div class="flex items-center gap-2">
                            <input 
                                v-model="searchPhone" 
                                type="text" 
                                class="db-field-control" 
                                :placeholder="$t('label.search_phone_number') || 'Search phone number...'"
                                style="min-width: 200px;"
                                @input="filterRows" />
                            <button v-if="searchPhone" class="db-btn py-2 text-white bg-gray-600" @click.prevent="clearSearch">
                                <i class="lab lab-close lab-font-size-16"></i>
                            </button>
                        </div>
                        <div class="dropdown-group">
                            <ExportComponent />
                            <div class="dropdown-list db-card-filter-dropdown-list transition-all duration-300 scale-y-0 origin-top">
                                <ExcelComponent :method="xls" />
                            </div>
                        </div>
                        <button class="db-btn py-2 text-white bg-green-600" @click.prevent="exportContacts">
                            <i class="lab lab-phone lab-font-size-16"></i>
                            <span>{{ $t("button.export_contacts") || "Export Contacts" }}</span>
                        </button>
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
                        <button v-if="permissionChecker('online_users_create')" class="db-btn py-2 text-white bg-primary" @click.prevent="openCreate">
                            <i class="lab lab-add-circle-line lab-font-size-16"></i>
                            <span>{{ $t("button.add") }}</span>
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
                            <th class="db-table-head-th">{{ $t("menu.campaigns") }}</th>
                            <th class="db-table-head-th">{{ $t("label.last_order") }}</th>
                            <th class="db-table-head-th hidden-print" v-if="permissionChecker('online_users_edit') || permissionChecker('online_users_delete')">
                                {{ $t("label.action") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="filteredRows.length > 0">
                        <tr class="db-table-body-tr" v-for="row in filteredRows" :key="row.id">
                            <td class="db-table-body-td">
                                <span dir="ltr">{{ row.whatsapp }}</span>
                            </td>
                            <td class="db-table-body-td">
                                {{ row.location }}
                            </td>
                            <td class="db-table-body-td">
                                <span v-if="row.campaign_name" class="text-primary font-medium">{{ row.campaign_name }}</span>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="db-table-body-td">
                                {{ row.last_order_at }}
                            </td>
                            <td class="db-table-body-td hidden-print" v-if="permissionChecker('online_users_edit') || permissionChecker('online_users_delete')">
                                <div class="flex justify-start items-center gap-1.5">
                                    <SmIconModalEditComponent v-if="permissionChecker('online_users_edit')" @click="openEdit(row)" />
                                    <SmIconDeleteComponent v-if="permissionChecker('online_users_delete')" @click="destroy(row.id)" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tbody class="db-table-body" v-else>
                        <tr class="db-table-body-tr">
                            <td class="db-table-body-td text-center" colspan="5">
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

    <!-- Create modal -->
    <div ref="createModal" class="modal ff-modal">
        <div class="modal-dialog max-w-[520px] p-0">
            <div class="modal-header p-4 border-b">
                <h3 class="text-base font-medium">{{ $t("button.add") }}</h3>
                <button class="modal-close" @click.prevent="closeCreate">
                    <i class="fa-regular fa-circle-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-4">
                <form @submit.prevent="save">
                    <div class="row">
                        <div class="col-12">
                            <label class="db-field-title after:hidden">{{ $t("label.whatsapp_number") }}</label>
                            <input v-model="form.whatsapp" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12">
                            <label class="db-field-title after:hidden">{{ $t("label.location") }}</label>
                            <input v-model="form.location" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12">
                            <label class="db-field-title after:hidden">{{ $t("menu.campaigns") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" 
                                v-model="form.campaign_id" 
                                :options="campaignsWithNone" 
                                label-by="name" 
                                value-by="id" 
                                :closeOnSelect="true" 
                                :searchable="true" 
                                :clearOnClose="true" 
                                placeholder="Select Campaign" 
                                search-placeholder="Search..." />
                        </div>
                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button class="db-btn py-2 text-white bg-primary" type="submit">
                                    <span>{{ $t("button.save") }}</span>
                                </button>
                                <button class="db-btn py-2 text-white bg-gray-600" type="button" @click.prevent="closeCreate">
                                    <span>{{ $t("button.cancel") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit modal -->
    <div ref="editModal" class="modal ff-modal">
        <div class="modal-dialog max-w-[520px] p-0">
            <div class="modal-header p-4 border-b">
                <h3 class="text-base font-medium">{{ $t("button.edit") }}</h3>
                <button class="modal-close" @click.prevent="closeEdit">
                    <i class="fa-regular fa-circle-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-4">
                <form @submit.prevent="update">
                    <div class="row">
                        <div class="col-12">
                            <label class="db-field-title after:hidden">{{ $t("label.whatsapp_number") }}</label>
                            <input v-model="editForm.whatsapp" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12">
                            <label class="db-field-title after:hidden">{{ $t("label.location") }}</label>
                            <input v-model="editForm.location" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12">
                            <label class="db-field-title after:hidden">{{ $t("menu.campaigns") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" 
                                v-model="editForm.campaign_id" 
                                :options="campaignsWithNone" 
                                label-by="name" 
                                value-by="id" 
                                :closeOnSelect="true" 
                                :searchable="true" 
                                :clearOnClose="true" 
                                placeholder="Select Campaign" 
                                search-placeholder="Search..." />
                        </div>
                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button class="db-btn py-2 text-white bg-primary" type="submit">
                                    <span>{{ $t("button.update") }}</span>
                                </button>
                                <button class="db-btn py-2 text-white bg-gray-600" type="button" @click.prevent="closeEdit">
                                    <span>{{ $t("button.cancel") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent.vue";
import SmIconModalEditComponent from "../components/buttons/SmIconModalEditComponent.vue";

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
        SmIconDeleteComponent,
        SmIconModalEditComponent,
    },
    data() {
        return {
            loading: { isActive: false },
            rows: [],
            campaigns: [],
            form: { whatsapp: "", location: "", campaign_id: null },
            editId: null,
            editForm: { whatsapp: "", location: "", campaign_id: null },
            searchPhone: "",
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
        filteredRows() {
            let rows = this.uniqueRows;
            
            // Filter by phone number search
            if (this.searchPhone && this.searchPhone.trim() !== "") {
                const searchTerm = this.searchPhone.trim().toLowerCase();
                rows = rows.filter(row => {
                    const phone = String(row.whatsapp || "").toLowerCase();
                    return phone.includes(searchTerm);
                });
            }
            
            return rows;
        },
        campaignsWithNone() {
            // Add "No Campaign" option at the beginning
            const noCampaignOption = {
                id: null,
                name: this.$t("label.no_campaign") || "No Campaign"
            };
            return [noCampaignOption, ...this.campaigns];
        },
    },
    mounted() {
        this.list();
        this.loadCampaigns();
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        loadCampaigns() {
            this.$store.dispatch("campaign/lists", { paginate: 0, status: 5 }).then((res) => {
                this.campaigns = res.data.data || [];
            }).catch(() => {
                this.campaigns = [];
            });
        },
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
        openCreate() {
            this.form = { whatsapp: "", location: "", campaign_id: null };
            appService.modalShow(this.$refs.createModal);
        },
        closeCreate() {
            appService.modalHide(this.$refs.createModal);
        },
        save() {
            this.loading.isActive = true;
            this.$store.dispatch("onlineUser/store", this.form).then(() => {
                this.loading.isActive = false;
                this.closeCreate();
                this.list();
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err?.response?.data?.message || "Failed to save");
            });
        },
        openEdit(row) {
            this.editId = row.id;
            this.editForm = { whatsapp: row.whatsapp || "", location: row.location || "", campaign_id: row.campaign_id || null };
            appService.modalShow(this.$refs.editModal);
        },
        closeEdit() {
            appService.modalHide(this.$refs.editModal);
        },
        update() {
            if (!this.editId) return;
            this.loading.isActive = true;
            this.$store.dispatch("onlineUser/update", { id: this.editId, data: this.editForm }).then(() => {
                this.loading.isActive = false;
                this.closeEdit();
                this.list();
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err?.response?.data?.message || "Failed to update");
            });
        },
        destroy(id) {
            appService.destroyConfirmation().then(() => {
                this.loading.isActive = true;
                this.$store.dispatch("onlineUser/destroy", id).then(() => {
                    this.loading.isActive = false;
                    this.list();
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err?.response?.data?.message || "Failed to delete");
                });
            });
        },
        filterRows() {
            // Filtering is handled by computed property filteredRows
            // This method is called on input to trigger reactivity
        },
        clearSearch() {
            this.searchPhone = "";
        },
        exportContacts() {
            try {
                // Get unique phone numbers from filtered rows
                const phoneNumbers = new Set();
                this.filteredRows.forEach(row => {
                    if (row.whatsapp && row.whatsapp.trim() !== "") {
                        // Normalize phone number - remove spaces and special characters except +
                        let phone = row.whatsapp.trim().replace(/[\s\-\(\)]/g, "");
                        // Ensure it starts with + if it doesn't
                        if (!phone.startsWith("+")) {
                            // If it starts with 994, add +
                            if (phone.startsWith("994")) {
                                phone = "+" + phone;
                            } else if (phone.startsWith("0")) {
                                // Replace leading 0 with +994
                                phone = "+994" + phone.substring(1);
                            } else {
                                phone = "+" + phone;
                            }
                        }
                        phoneNumbers.add(phone);
                    }
                });

                if (phoneNumbers.size === 0) {
                    alertService.error(this.$t("message.no_phone_numbers_to_export") || "No phone numbers to export");
                    return;
                }

                // Generate vCard content
                // vCard format: each contact is a separate vCard entry
                let vcardContent = "";
                phoneNumbers.forEach(phone => {
                    // vCard format - only phone number, no name
                    vcardContent += "BEGIN:VCARD\n";
                    vcardContent += "VERSION:3.0\n";
                    vcardContent += `TEL:${phone}\n`;
                    vcardContent += "END:VCARD\n";
                });

                // Create blob and download
                const blob = new Blob([vcardContent], { type: "text/vcard;charset=utf-8" });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "contacts.vcf";
                link.click();
                URL.revokeObjectURL(link.href);

                alertService.success(this.$t("message.contacts_exported") || `Exported ${phoneNumbers.size} contact(s)`);
            } catch (error) {
                console.error("Error exporting contacts:", error);
                alertService.error(this.$t("message.export_failed") || "Failed to export contacts");
            }
        },
    },
};
</script>


