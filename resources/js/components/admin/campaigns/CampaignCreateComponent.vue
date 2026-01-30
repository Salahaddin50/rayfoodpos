<template>
    <LoadingComponent :props="loading" />
    <SmSidebarModalCreateComponent :props="addButton" @click="addReset" />

    <div id="sidebar" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">{{ $t('menu.campaigns') }}</h3>
            <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
        </div>
        <div class="drawer-body">
            <form @submit.prevent="save">
                <div class="form-row">
                    <div class="form-col-12">
                        <label for="name" class="db-field-title required">{{ $t("label.name") }}</label>
                        <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                            id="name" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.name">{{
                            errors.name[0]
                        }}</small>
                    </div>
                    <div class="form-col-12">
                        <label for="description" class="db-field-title">{{ $t("label.description") }}</label>
                        <textarea v-model="props.form.description" v-bind:class="errors.description ? 'invalid' : ''"
                            id="description" class="db-field-control" rows="3"></textarea>
                        <small class="db-field-alert" v-if="errors.description">{{ errors.description[0] }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="type" class="db-field-title required">{{ $t("label.type") }}</label>
                        <vue-select class="db-field-control f-b-custom-select" id="type"
                            v-model="props.form.type" :options="[
                                { id: campaignTypeEnum.PERCENTAGE, name: 'Percentage' },
                                { id: campaignTypeEnum.ITEM, name: 'Item' },
                            ]" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                            :clearOnClose="true" placeholder="--" search-placeholder="--"
                            v-bind:class="errors.type ? 'invalid' : ''" />
                        <small class="db-field-alert" v-if="errors.type">{{ errors.type[0] }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6" v-if="props.form.type === campaignTypeEnum.PERCENTAGE">
                        <label for="discount_value" class="db-field-title required">
                            {{ $t("label.discount_percentage") }}
                        </label>
                        <input v-model="props.form.discount_value" v-on:keypress="floatNumber($event)"
                            v-bind:class="errors.discount_value ? 'invalid' : ''" type="text" id="discount_value"
                            class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.discount_value">{{ errors.discount_value[0] }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6" v-if="props.form.type === campaignTypeEnum.ITEM">
                        <label for="required_purchases" class="db-field-title required">
                            Required Purchases (e.g., 8)
                        </label>
                        <input v-model="props.form.required_purchases" v-on:keypress="onlyNumber($event)"
                            v-bind:class="errors.required_purchases ? 'invalid' : ''" type="text" id="required_purchases"
                            class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.required_purchases">{{ errors.required_purchases[0] }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6" v-if="props.form.type === campaignTypeEnum.ITEM">
                        <label for="free_item_id" class="db-field-title">Free Item</label>
                        <vue-select class="db-field-control f-b-custom-select" id="free_item_id"
                            v-model="props.form.free_item_id" :options="items" label-by="name" value-by="id"
                            :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="Select Item"
                            v-bind:class="errors.free_item_id ? 'invalid' : ''" />
                        <small class="db-field-alert" v-if="errors.free_item_id">{{ errors.free_item_id[0] }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="start_date" class="db-field-title">{{ $t("label.start_date") }}</label>
                        <Datepicker hideInputIcon autoApply v-model="props.form.start_date" :enableTimePicker="true"
                            :is24="false" :monthChangeOnScroll="false" utc="false"
                            :input-class-name="errors.start_date ? 'invalid' : ''">
                            <template #am-pm-button="{ toggle, value }">
                                <button @click="toggle">{{ value }}</button>
                            </template>
                        </Datepicker>
                        <small class="db-field-alert" v-if="errors.start_date">{{ errors.start_date[0] }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="end_date" class="db-field-title">{{ $t("label.end_date") }}</label>
                        <Datepicker hideInputIcon autoApply v-model="props.form.end_date" :enableTimePicker="true"
                            :is24="false" :monthChangeOnScroll="false" utc="false"
                            :input-class-name="errors.end_date ? 'invalid' : ''">
                            <template #am-pm-button="{ toggle, value }">
                                <button @click="toggle">{{ value }}</button>
                            </template>
                        </Datepicker>
                        <small class="db-field-alert" v-if="errors.end_date">{{
                            errors.end_date[0]
                        }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label class="db-field-title">{{ $t("label.status") }}</label>
                        <div class="db-field-radio-group">
                            <div class="db-field-radio">
                                <div class="custom-radio">
                                    <input type="radio" v-model="props.form.status" id="active"
                                        :value="enums.statusEnum.ACTIVE" class="custom-radio-field" checked />
                                    <span class="custom-radio-span"></span>
                                </div>
                                <label for="active" class="db-field-label">{{
                                    $t("label.active")
                                }}</label>
                            </div>
                            <div class="db-field-radio">
                                <div class="custom-radio">
                                    <input type="radio" class="custom-radio-field" v-model="props.form.status"
                                        id="inactive" :value="enums.statusEnum.INACTIVE" />
                                    <span class="custom-radio-span"></span>
                                </div>
                                <label for="inactive" class="db-field-label">{{
                                    $t("label.inactive")
                                    }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-col-12">
                        <div class="flex flex-wrap gap-3 mt-4">
                            <button type="submit" class="db-btn py-2 text-white bg-primary">
                                <i class="lab lab-save"></i>
                                <span>{{ $t("label.save") }}</span>
                            </button>

                            <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                <i class="lab lab-close"></i>
                                <span>{{ $t("button.close") }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
<script>
import SmSidebarModalCreateComponent from "../components/buttons/SmSidebarModalCreateComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import LoadingComponent from "../components/LoadingComponent";
import statusEnum from "../../../enums/modules/statusEnum";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

const campaignTypeEnum = {
    PERCENTAGE: 5,
    ITEM: 10
};

export default {
    name: "CampaignCreateComponent",
    components: { SmSidebarModalCreateComponent, LoadingComponent, Datepicker },
    props: ["props"],
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
            errors: {},
            items: [],
        };
    },
    mounted() {
        this.loadItems();
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_campaign') };
        },
    },
    methods: {
        floatNumber(e) {
            return appService.floatNumber(e);
        },
        onlyNumber(e) {
            return appService.onlyNumber(e);
        },
        loadItems: function () {
            // Load items for the dropdown
            this.$store.dispatch("item/lists", { paginate: 0 }).then((res) => {
                this.items = res.data.data;
            }).catch((err) => {
                // Handle error
            });
        },
        reset: function () {
            appService.sideDrawerHide();
            this.$store.dispatch("campaign/reset").then().catch();
            this.errors = {};
            this.props.form = {
                name: "",
                description: "",
                type: campaignTypeEnum.PERCENTAGE,
                discount_value: "",
                free_item_id: null,
                required_purchases: null,
                status: statusEnum.ACTIVE,
                start_date: "",
                end_date: "",
            };
        },
        addReset: function () {
            this.reset();
            appService.sideDrawerShow();
        },
        save: function () {
            try {
                this.loading.isActive = true;
                this.errors = {};
                this.$store.dispatch("campaign/save", {
                    form: this.props.form,
                    search: this.props.search,
                }).then((res) => {
                    appService.sideDrawerHide();
                    this.loading.isActive = false;
                    alertService.successFlip(
                        this.$store.getters["campaign/temp"].isEditing ? 1 : 0,
                        this.$t("menu.campaigns")
                    );
                    this.reset();
                }).catch((err) => {
                    this.loading.isActive = false;
                    if (err.response.data.errors !== undefined) {
                        this.errors = err.response.data.errors;
                    } else {
                        alertService.error(err.response.data.message);
                    }
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
    },
};
</script>
