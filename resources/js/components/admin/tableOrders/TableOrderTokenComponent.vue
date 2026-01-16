<template>
    <LoadingComponent :props="loading" />
    <button type="button" @click="tokenModal" data-modal="#tokenModal" class="db-btn h-[37px] text-white bg-primary">
        <i class="lab lab-add-circle-line"></i>
        <span class="text-sm capitalize text-white">{{ $t("button.add_token") }}</span>
    </button>

    <div id="tokenModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("label.token") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click.prevent="resetModal"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="rejectOrder">
                    <div class="form-row">
                        <div class="form-col-12">
                            <label for="name" class="db-field-title required">
                                {{ $t("label.token_no") }}
                            </label>
                            <div class="flex gap-2">
                                <input v-model="form.token" v-on:keypress="onlyNumber($event)" v-bind:class="error ? 'invalid' : ''" type="text" id="name"
                                    class="db-field-control flex-1" />
                                <button @click.stop.prevent="generateToken" type="button"
                                    class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg text-white bg-primary">
                                    <i class="lab lab-add-circle-line"></i>
                                    <span class="capitalize text-sm font-bold">Number</span>
                                </button>
                                <button @click.stop.prevent="resetToken" type="button"
                                    class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg text-white bg-primary">
                                    <i class="lab lab-refresh-line"></i>
                                    <span class="capitalize text-sm font-bold">Reset</span>
                                </button>
                            </div>
                            <small class="db-field-alert" v-if="error">
                                {{ error }}
                            </small>
                        </div>
                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click.prevent="resetModal">
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
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import LoadingComponent from "../components/LoadingComponent";

export default {
    name: "TableOrderTokenComponent",
    components: {
        LoadingComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            form: {
                token: "",
            },
            error: "",
        };
    },
    computed: {
        order: function () {
            return this.$store.getters["tableOrder/show"];
        },
        orderBranch: function () {
            return this.$store.getters["tableOrder/orderBranch"];
        },
        branchId: function () {
            // Try to get branch_id from order first, then from orderBranch
            return this.order?.branch_id || this.orderBranch?.id;
        },
    },
    methods: {
        tokenModal: function () {
            appService.modalShow("#tokenModal");
        },
        resetModal: function () {
            appService.modalHide("#tokenModal");
            this.form.token = "";
            this.error = "";
        },
        onlyNumber: function (e) {
            return appService.onlyNumber(e);
        },
        generateToken: function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            if (!this.branchId) {
                alertService.error("Branch information not available");
                return false;
            }
            
            this.loading.isActive = true;
            this.$store.dispatch('token/generate', {
                branch_id: this.branchId
            }).then((res) => {
                let token = res.data.data.token;
                
                // Add prefix based on order type
                if (this.order.whatsapp_number) {
                    // Online order - add 000 prefix
                    token = "000" + token;
                } else if (this.order.dining_table_id) {
                    // Table order - add 00 prefix
                    token = "00" + token;
                }
                
                this.form.token = token;
                this.loading.isActive = false;
                alertService.success("Token generated: " + token);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response?.data?.message || "Token generation failed");
            });
            
            return false;
        },
        resetToken: function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            if (!this.branchId) {
                alertService.error("Branch information not available");
                return false;
            }
            
            if (!confirm("Are you sure you want to reset the token counter? Next token will start from 1.")) {
                return false;
            }
            
            this.loading.isActive = true;
            this.$store.dispatch('token/reset', {
                branch_id: this.branchId
            }).then((res) => {
                this.form.token = "";
                this.loading.isActive = false;
                alertService.success("Token counter reset successfully");
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response?.data?.message || "Token reset failed");
            });
            
            return false;
        },
        rejectOrder: function () {
            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch("tableOrder/tokenCreate", {
                        id: this.$route.params.id,
                        token: this.form.token,
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        appService.modalHide();
                        this.form = {
                            token: "",
                        };
                        this.error = "";
                        alertService.successFlip(0, this.$t("label.token"));
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        this.error = err.response.data.message;
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
    },
};
</script>