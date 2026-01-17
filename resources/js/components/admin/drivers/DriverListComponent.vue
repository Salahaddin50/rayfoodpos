<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title flex items-center gap-2">
                    <i class="lab lab-delivery-boy lab-font-size-20"></i>
                    <span>{{ $t("menu.drivers") }}</span>
                </h3>
                <div class="db-card-filter">
                    <div class="flex items-center gap-3">
                        <div class="text-xs text-gray-500" v-if="branch && branch.name">
                            {{ $t("label.branch") }}: <b class="font-medium">{{ branch.name }}</b>
                        </div>
                        <div v-if="permissionChecker('drivers')" class="dropdown-group">
                            <ExportComponent />
                            <div class="dropdown-list db-card-filter-dropdown-list transition-all duration-300 scale-y-0 origin-top">
                                <ExcelComponent :method="xls" />
                            </div>
                        </div>
                        <div v-if="permissionChecker('drivers_create')" class="dropdown-group">
                            <ImportComponent />
                            <div class="dropdown-list db-card-filter-dropdown-list transition-all duration-300 scale-y-0 origin-top">
                                <SampleFileComponent @click="downloadSample" />
                                <UploadFileComponent :dataModal="'driverUpload'" @click="uploadModal('#driverUpload')" />
                            </div>
                        </div>
                        <button v-if="permissionChecker('drivers_create')" class="db-btn py-2 text-white bg-primary" @click.prevent="openCreate">
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
                            <th class="db-table-head-th">{{ $t("label.name") }}</th>
                            <th class="db-table-head-th">{{ $t("label.transport_type") }}</th>
                            <th class="db-table-head-th">{{ $t("label.whatsapp_number") }}</th>
                            <th class="db-table-head-th hidden-print" v-if="permissionChecker('drivers_edit') || permissionChecker('drivers_delete')">
                                {{ $t("label.action") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="rows.length > 0">
                        <tr class="db-table-body-tr" v-for="row in rows" :key="row.id">
                            <td class="db-table-body-td">{{ row.name }}</td>
                            <td class="db-table-body-td">{{ row.transport_type }}</td>
                            <td class="db-table-body-td"><span dir="ltr">{{ row.whatsapp }}</span></td>
                            <td class="db-table-body-td hidden-print" v-if="permissionChecker('drivers_edit') || permissionChecker('drivers_delete')">
                                <div class="flex justify-start items-center gap-1.5">
                                    <SmIconModalEditComponent v-if="permissionChecker('drivers_edit')" @click="openEdit(row)" />
                                    <SmIconDeleteComponent v-if="permissionChecker('drivers_delete')" @click="destroy(row.id)" />
                                </div>
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

    <!-- Create modal -->
    <div ref="createModal" class="modal ff-modal">
        <div class="modal-dialog max-w-[520px] p-0">
            <div class="modal-header p-4 border-b">
                <h3 class="text-base font-medium">{{ $t("menu.drivers") }}</h3>
                <button class="modal-close" @click.prevent="closeCreate">
                    <i class="fa-regular fa-circle-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-4">
                <form @submit.prevent="save">
                    <div class="row">
                        <div class="col-12 sm:col-6">
                            <label class="db-field-title after:hidden">{{ $t("label.name") }}</label>
                            <input v-model="form.name" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6">
                            <label class="db-field-title after:hidden">{{ $t("label.transport_type") }}</label>
                            <input v-model="form.transport_type" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12">
                            <label class="db-field-title after:hidden">{{ $t("label.whatsapp_number") }}</label>
                            <input v-model="form.whatsapp" type="text" class="db-field-control" />
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
                        <div class="col-12 sm:col-6">
                            <label class="db-field-title after:hidden">{{ $t("label.name") }}</label>
                            <input v-model="editForm.name" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6">
                            <label class="db-field-title after:hidden">{{ $t("label.transport_type") }}</label>
                            <input v-model="editForm.transport_type" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12">
                            <label class="db-field-title after:hidden">{{ $t("label.whatsapp_number") }}</label>
                            <input v-model="editForm.whatsapp" type="text" class="db-field-control" />
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

    <DriverUploadComponent v-on:list="list" />
</template>

<script>
import LoadingComponent from "../components/LoadingComponent.vue";
import ExportComponent from "../components/buttons/export/ExportComponent.vue";
import ExcelComponent from "../components/buttons/export/ExcelComponent.vue";
import ImportComponent from "../components/buttons/import/ImportComponent.vue";
import SampleFileComponent from "../components/buttons/import/SampleFileComponent.vue";
import UploadFileComponent from "../components/buttons/import/UploadFileComponent.vue";
import DriverUploadComponent from "./DriverUploadComponent.vue";
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent.vue";
import SmIconModalEditComponent from "../components/buttons/SmIconModalEditComponent.vue";

export default {
    name: "DriverListComponent",
    components: {
        LoadingComponent,
        ExportComponent,
        ExcelComponent,
        ImportComponent,
        SampleFileComponent,
        UploadFileComponent,
        DriverUploadComponent,
        SmIconDeleteComponent,
        SmIconModalEditComponent,
    },
    data() {
        return {
            loading: { isActive: false },
            rows: [],
            form: {
                name: "",
                transport_type: "",
                whatsapp: "",
            },
            editId: null,
            editForm: {
                name: "",
                transport_type: "",
                whatsapp: "",
            },
        };
    },
    computed: {
        branch() {
            return this.$store.getters["backendGlobalState/branchShow"];
        },
        drivers() {
            return this.$store.getters["driver/lists"];
        },
    },
    mounted() {
        this.list();
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        list() {
            this.loading.isActive = true;
            this.$store
                .dispatch("driver/lists", { paginate: 0, order_column: "id", order_type: "desc" })
                .then(() => {
                    this.rows = this.drivers || [];
                    this.loading.isActive = false;
                })
                .catch(() => {
                    this.loading.isActive = false;
                });
        },
        openCreate() {
            appService.modalShow(this.$refs.createModal);
        },
        closeCreate() {
            appService.modalHide(this.$refs.createModal);
        },
        openEdit(row) {
            this.editId = row.id;
            this.editForm = {
                name: row.name || "",
                transport_type: row.transport_type || "",
                whatsapp: row.whatsapp || "",
            };
            appService.modalShow(this.$refs.editModal);
        },
        closeEdit() {
            appService.modalHide(this.$refs.editModal);
        },
        uploadModal(id) {
            appService.modalShow(id);
        },
        xls() {
            this.loading.isActive = true;
            this.$store
                .dispatch("driver/export", { paginate: 0 })
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t("menu.drivers");
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err?.response?.data?.message || "Export failed");
                });
        },
        downloadSample() {
            this.loading.isActive = true;
            this.$store
                .dispatch("driver/downloadSample")
                .then((res) => {
                    this.loading.isActive = false;
                    const url = window.URL.createObjectURL(new Blob([res.data]));
                    const link = document.createElement("a");
                    link.href = url;
                    link.download = "Driver Import Sample.xlsx";
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch(() => {
                    this.loading.isActive = false;
                });
        },
        save() {
            this.loading.isActive = true;
            this.$store
                .dispatch("driver/store", this.form)
                .then(() => {
                    this.loading.isActive = false;
                    this.form = { name: "", transport_type: "", whatsapp: "" };
                    this.closeCreate();
                    this.list();
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err?.response?.data?.message || "Failed to save driver");
                });
        },
        update() {
            if (!this.editId) return;
            this.loading.isActive = true;
            this.$store
                .dispatch("driver/update", { id: this.editId, data: this.editForm })
                .then(() => {
                    this.loading.isActive = false;
                    this.closeEdit();
                    this.list();
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err?.response?.data?.message || "Failed to update driver");
                });
        },
        destroy(id) {
            appService.destroyConfirmation().then(() => {
                this.loading.isActive = true;
                this.$store.dispatch("driver/destroy", id).then(() => {
                    this.loading.isActive = false;
                    this.list();
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err?.response?.data?.message || "Failed to delete driver");
                });
            });
        },
    },
};
</script>


