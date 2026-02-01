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
                            <th class="db-table-head-th">{{ $t("label.campaign_status") || "Campaign Status" }}</th>
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
                                <div v-if="row.campaign_progress" class="flex items-center gap-2">
                                    <span v-if="row.campaign_progress.type === 'percentage'" class="text-sm text-blue-600">
                                        {{ row.campaign_progress.discount_value }}% {{ $t("label.off") || "off" }}
                                    </span>
                                    <span v-else-if="row.campaign_progress.type === 'item'" class="text-sm">
                                        <span v-if="row.campaign_progress.is_completed" class="text-green-600 font-medium">
                                            ✓ {{ $t("label.completed") || "Completed" }}
                                        </span>
                                        <span v-else class="text-gray-700">
                                            {{ row.campaign_progress.current_progress || 0 }} / {{ row.campaign_progress.required_purchases || 0 }}
                                        </span>
                                    </span>
                                    <button 
                                        v-if="permissionChecker('online_users_edit') && row.campaign_progress" 
                                        class="text-xs text-blue-600 hover:text-blue-800 underline"
                                        @click="openCampaignStatusModal(row)"
                                    >
                                        {{ $t("button.manage") || "Manage" }}
                                    </button>
                                </div>
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

    <!-- Campaign Status Modal -->
    <div ref="campaignStatusModal" class="modal ff-modal">
        <div class="modal-dialog max-w-[600px] p-0">
            <div class="modal-header p-4 border-b">
                <h3 class="text-base font-medium">{{ $t("label.campaign_status") || "Campaign Status" }}</h3>
                <button class="modal-close" @click.prevent="closeCampaignStatusModal">
                    <i class="fa-regular fa-circle-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-4" v-if="selectedCampaignUser">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>{{ $t("label.whatsapp_number") }}:</strong> {{ selectedCampaignUser.whatsapp }}
                    </p>
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>{{ $t("menu.campaigns") }}:</strong> {{ selectedCampaignUser.campaign_name || '-' }}
                    </p>
                    <p v-if="selectedCampaignUser.campaign_joined_at" class="text-sm text-gray-600 mb-4">
                        <strong>{{ $t("label.joined_at") || "Joined At" }}:</strong> {{ selectedCampaignUser.campaign_joined_at }}
                    </p>
                </div>

                <div v-if="selectedCampaignUser.campaign_progress" class="space-y-4">
                    <!-- Percentage Campaign -->
                    <div v-if="selectedCampaignUser.campaign_progress.type === 'percentage'" class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm font-medium text-blue-900">
                            {{ $t("label.percentage_campaign") || "Percentage Campaign" }}
                        </p>
                        <p class="text-sm text-blue-700 mt-1">
                            {{ selectedCampaignUser.campaign_progress.discount_value }}% {{ $t("label.off") || "off" }}
                        </p>
                    </div>

                    <!-- Item Campaign -->
                    <div v-else-if="selectedCampaignUser.campaign_progress.type === 'item'" class="space-y-3">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">{{ $t("label.progress") || "Progress" }}:</span>
                                <span class="text-sm text-gray-900 font-semibold">
                                    {{ selectedCampaignUser.campaign_progress.current_progress || 0 }} / {{ selectedCampaignUser.campaign_progress.required_purchases || 0 }} {{ $t("label.orders") || "Orders" }}
                                </span>
                            </div>
                            <div v-if="selectedCampaignUser.campaign_progress.free_item" class="text-xs text-gray-600 mt-2">
                                <strong>{{ $t("label.free_item") || "Free Item" }}:</strong> {{ selectedCampaignUser.campaign_progress.free_item.name }}
                                <span v-if="selectedCampaignUser.campaign_progress.free_item.category_name" class="text-blue-600">
                                    ({{ selectedCampaignUser.campaign_progress.free_item.category_name }})
                                </span>
                            </div>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg" v-if="selectedCampaignUser.campaign_progress.rewards_available > 0">
                            <p class="text-sm font-medium text-green-900">
                                {{ $t("label.rewards_available") || "Rewards Available" }}: {{ selectedCampaignUser.campaign_progress.rewards_available }}
                            </p>
                        </div>

                        <div class="bg-yellow-50 p-4 rounded-lg" v-if="selectedCampaignUser.campaign_progress.is_completed">
                            <p class="text-sm font-medium text-yellow-900">
                                ✓ {{ $t("label.campaign_completed") || "Campaign Completed" }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-6">
                    <button 
                        v-if="selectedCampaignUser.campaign_progress && selectedCampaignUser.campaign_progress.type === 'item'"
                        class="db-btn py-2 text-white bg-yellow-600" 
                        type="button" 
                        @click.prevent="resetCampaignProgress"
                    >
                        <span>{{ $t("button.reset_progress") || "Reset Progress" }}</span>
                    </button>
                    <button 
                        v-if="selectedCampaignUser.campaign_id"
                        class="db-btn py-2 text-white bg-red-600" 
                        type="button" 
                        @click.prevent="removeCampaign"
                    >
                        <span>{{ $t("button.remove_campaign") || "Remove Campaign" }}</span>
                    </button>
                    <button class="db-btn py-2 text-white bg-gray-600" type="button" @click.prevent="closeCampaignStatusModal">
                        <span>{{ $t("button.close") || "Close" }}</span>
                    </button>
                </div>
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
            selectedCampaignUser: null,
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
                // Get unique phone numbers from ALL online users (not just filtered)
                const phoneNumbers = new Set();
                const rowsToExport = this.uniqueRows; // Use all unique rows, not filtered
                
                rowsToExport.forEach((row) => {
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
                // Use CRLF line endings for better compatibility
                let vcardContent = "";
                phoneNumbers.forEach(phone => {
                    // vCard format - only phone number, no name
                    vcardContent += "BEGIN:VCARD\r\n";
                    vcardContent += "VERSION:3.0\r\n";
                    vcardContent += `TEL:${phone}\r\n`;
                    vcardContent += "END:VCARD\r\n";
                });

                // Create blob and download
                const blob = new Blob([vcardContent], { type: "text/vcard;charset=utf-8" });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "contacts.vcf";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);

                alertService.success(this.$t("message.contacts_exported") || `Exported ${phoneNumbers.size} contact(s)`);
            } catch (error) {
                console.error("Error exporting contacts:", error);
                alertService.error(this.$t("message.export_failed") || "Failed to export contacts");
            }
        },
        openCampaignStatusModal(row) {
            this.selectedCampaignUser = row;
            appService.modalShow(this.$refs.campaignStatusModal);
        },
        closeCampaignStatusModal() {
            appService.modalHide(this.$refs.campaignStatusModal);
            this.selectedCampaignUser = null;
        },
        resetCampaignProgress() {
            if (!this.selectedCampaignUser) return;
            
            appService.destroyConfirmation(this.$t("message.confirm_reset_campaign") || "Are you sure you want to reset campaign progress? This will start counting orders from now.").then(() => {
                this.loading.isActive = true;
                this.$store.dispatch("onlineUser/updateCampaignProgress", {
                    id: this.selectedCampaignUser.id,
                    action: 'reset'
                }).then(() => {
                    this.loading.isActive = false;
                    alertService.success(this.$t("message.campaign_progress_reset") || "Campaign progress reset successfully");
                    this.list();
                    this.closeCampaignStatusModal();
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err?.response?.data?.message || "Failed to reset campaign progress");
                });
            });
        },
        removeCampaign() {
            if (!this.selectedCampaignUser) return;
            
            appService.destroyConfirmation(this.$t("message.confirm_remove_campaign") || "Are you sure you want to remove this campaign from the user?").then(() => {
                this.loading.isActive = true;
                this.$store.dispatch("onlineUser/updateCampaignProgress", {
                    id: this.selectedCampaignUser.id,
                    action: 'remove'
                }).then(() => {
                    this.loading.isActive = false;
                    alertService.success(this.$t("message.campaign_removed") || "Campaign removed successfully");
                    this.list();
                    this.closeCampaignStatusModal();
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err?.response?.data?.message || "Failed to remove campaign");
                });
            });
        },
    },
};
</script>


