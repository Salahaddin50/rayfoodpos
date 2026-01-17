<template>
    <LoadingComponent :props="loading" />
    <div id="driverUpload" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("menu.drivers") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12">
                            <label class="db-field-title required">{{ $t("label.upload_file") }} ({{
                                $t("label.xlsx") }})</label>
                            <input @change="changeFile" v-bind:class="errors.file ? 'invalid' : ''" id="file"
                                type="file" class="db-field-control" ref="fileProperty" accept=".xlsx, .xls" />
                            <small class="db-field-alert" v-if="errors.file">{{ errors.file[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t("button.close") }}</span>
                                </button>

                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t("button.save") }}</span>
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
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "DriverUploadComponent",
    components: { LoadingComponent },
    emits: ["list"],
    data() {
        return {
            loading: { isActive: false },
            file: "",
            errors: {},
        };
    },
    methods: {
        reset() {
            appService.modalHide();
            this.file = "";
            this.errors = {};
            this.$refs.fileProperty.value = null;
        },
        changeFile(e) {
            this.file = e.target.files[0];
        },
        save() {
            const fd = new FormData();
            if (this.file) fd.append("file", this.file);
            this.loading.isActive = true;
            this.$store
                .dispatch("driver/import", { form: fd })
                .then(() => {
                    this.loading.isActive = false;
                    appService.modalHide();
                    alertService.successFlip(0, this.$t("menu.drivers"));
                    this.file = "";
                    this.errors = {};
                    this.$refs.fileProperty.value = null;
                    this.$emit("list");
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    if (err.response?.data?.message) {
                        alertService.error(err.response.data.message);
                    } else {
                        this.errors = err.response?.data?.errors || {};
                    }
                });
        },
    },
};
</script>


