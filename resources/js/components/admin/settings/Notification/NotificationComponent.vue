<template>
    <LoadingComponent :props="loading" />

    <div id="company" class="db-card db-tab-div active">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ $t("menu.notification") }}</h3>
        </div>
        <div class="db-card-body">
            <form @submit.prevent="save">
                <div class="form-row">
                    <div class="form-col-12 sm:form-col-6">
                        <label for="notification_fcm_topic" class="db-field-title required">
                            {{ $t("label.notification_fcm_public_vapid_key") }}
                        </label>
                        <input v-model="form.notification_fcm_public_vapid_key"
                            v-bind:class="errors.notification_fcm_public_vapid_key ? 'invalid' : ''" type="text"
                            id="notification_fcm_topic" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.notification_fcm_public_vapid_key">{{
                            errors.notification_fcm_public_vapid_key[0]
                        }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="notification_fcm_api_key" class="db-field-title required">
                            {{ $t("label.notification_fcm_api_key") }}
                        </label>
                        <input v-model="form.notification_fcm_api_key"
                            v-bind:class="errors.notification_fcm_api_key ? 'invalid' : ''" type="text"
                            id="notification_fcm_api_key" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.notification_fcm_api_key">{{
                            errors.notification_fcm_api_key[0]
                        }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="notification_fcm_auth_domain" class="db-field-title required">
                            {{ $t("label.notification_fcm_auth_domain") }}
                        </label>
                        <input v-model="form.notification_fcm_auth_domain"
                            v-bind:class="errors.notification_fcm_auth_domain ? 'invalid' : ''" type="text"
                            id="notification_fcm_auth_domain" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.notification_fcm_auth_domain">{{
                            errors.notification_fcm_auth_domain[0]
                        }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="notification_fcm_project_id" class="db-field-title required">
                            {{ $t("label.notification_fcm_project_id") }}
                        </label>
                        <input v-model="form.notification_fcm_project_id"
                            v-bind:class="errors.notification_fcm_project_id ? 'invalid' : ''" type="text"
                            id="notification_fcm_project_id" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.notification_fcm_project_id">{{
                            errors.notification_fcm_project_id[0]
                        }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="notification_fcm_storage_bucket" class="db-field-title required">
                            {{ $t("label.notification_fcm_storage_bucket") }}
                        </label>
                        <input v-model="form.notification_fcm_storage_bucket"
                            v-bind:class="errors.notification_fcm_storage_bucket ? 'invalid' : ''" type="text"
                            id="notification_fcm_storage_bucket" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.notification_fcm_storage_bucket">{{
                            errors.notification_fcm_storage_bucket[0]
                        }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="notification_fcm_messaging_sender_id" class="db-field-title required">
                            {{ $t("label.notification_fcm_messaging_sender_id") }}
                        </label>
                        <input v-model="form.notification_fcm_messaging_sender_id"
                            v-bind:class="errors.notification_fcm_messaging_sender_id ? 'invalid' : ''" type="text"
                            id="notification_fcm_messaging_sender_id" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.notification_fcm_messaging_sender_id">{{
                            errors.notification_fcm_messaging_sender_id[0]
                        }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="notification_fcm_app_id" class="db-field-title required">
                            {{ $t("label.notification_fcm_app_id") }}
                        </label>
                        <input v-model="form.notification_fcm_app_id"
                            v-bind:class="errors.notification_fcm_app_id ? 'invalid' : ''" type="text"
                            id="notification_fcm_app_id" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.notification_fcm_app_id">{{
                            errors.notification_fcm_app_id[0]
                        }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="notification_fcm_measurement_id" class="db-field-title required">
                            {{ $t("label.notification_fcm_measurement_id") }}
                        </label>
                        <input v-model="form.notification_fcm_measurement_id"
                            v-bind:class="errors.notification_fcm_measurement_id ? 'invalid' : ''" type="text"
                            id="notification_fcm_measurement_id" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.notification_fcm_measurement_id">{{
                            errors.notification_fcm_measurement_id[0]
                        }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="notification_fcm_json_file" class="db-field-title required">
                            {{ $t("label.file") }} ({{ $t("label.json") }})
                        </label>
                        <input @change="changeFile" v-bind:class="errors.notification_fcm_json_file ? 'invalid' : ''"
                            id="notification_fcm_json_file" type="file" class="db-field-control" ref="fileProperty"
                            accept="application/json" />
                        <small class="db-field-alert" v-if="errors.notification_fcm_json_file">{{
                            errors.notification_fcm_json_file[0]
                        }}</small>
                    </div>

                    <div class="form-col-12">
                        <button type="submit" class="db-btn text-white bg-primary">
                            <i class="lab lab-save"></i>
                            <span>{{ $t("button.save") }}</span>
                        </button>

                        <button
                            type="button"
                            @click="sendTestPush"
                            class="db-btn ltr:ml-3 rtl:mr-3 text-white bg-green-600">
                            <i class="lab lab-notification"></i>
                            <span>Send test push</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../../components/LoadingComponent";
import alertService from "../../../../services/alertService";

export default {
    name: "NotificationComponent",
    components: { LoadingComponent, alertService },
    data() {
        return {
            loading: {
                isActive: false,
            },
            form: {
                notification_fcm_api_key: "",
                notification_fcm_auth_domain: "",
                notification_fcm_project_id: "",
                notification_fcm_storage_bucket: "",
                notification_fcm_messaging_sender_id: "",
                notification_fcm_app_id: "",
                notification_fcm_measurement_id: "",
                notification_fcm_public_vapid_key: "",
            },
            errors: {},
            notification_fcm_json_file: ""
        };
    },
    mounted() {
        try {
            this.loading.isActive = true;
            this.$store.dispatch("notification/lists").then((res) => {
                this.form = {
                    notification_fcm_api_key: res.data.data.notification_fcm_api_key,
                    notification_fcm_public_vapid_key: res.data.data.notification_fcm_public_vapid_key,
                    notification_fcm_auth_domain: res.data.data.notification_fcm_auth_domain,
                    notification_fcm_project_id: res.data.data.notification_fcm_project_id,
                    notification_fcm_storage_bucket: res.data.data.notification_fcm_storage_bucket,
                    notification_fcm_messaging_sender_id: res.data.data.notification_fcm_messaging_sender_id,
                    notification_fcm_app_id: res.data.data.notification_fcm_app_id,
                    notification_fcm_measurement_id: res.data.data.notification_fcm_measurement_id,
                };
                this.loading.isActive = false;
            })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        } catch (err) {
            this.loading.isActive = false;
            alertService.error(err);
        }
    },
    methods: {
        changeFile: function (e) {
            this.notification_fcm_json_file = e.target.files[0];
        },
        save: function () {
            try {
                const fd = new FormData();
                fd.append("notification_fcm_public_vapid_key", this.form.notification_fcm_public_vapid_key);
                fd.append("notification_fcm_api_key", this.form.notification_fcm_api_key);
                fd.append("notification_fcm_auth_domain", this.form.notification_fcm_auth_domain);
                fd.append("notification_fcm_project_id", this.form.notification_fcm_project_id);
                fd.append("notification_fcm_storage_bucket", this.form.notification_fcm_storage_bucket);
                fd.append("notification_fcm_messaging_sender_id", this.form.notification_fcm_messaging_sender_id);
                fd.append("notification_fcm_app_id", this.form.notification_fcm_app_id);
                fd.append("notification_fcm_measurement_id", this.form.notification_fcm_measurement_id);
                if (this.notification_fcm_json_file) {
                    fd.append("notification_fcm_json_file", this.notification_fcm_json_file);
                }
                this.loading.isActive = true;
                this.$store
                    .dispatch("notification/save", { form: fd })
                    .then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(
                            res.config.method === "put" ?? 0,
                            this.$t("menu.notification")
                        );
                        navigator.serviceWorker.getRegistrations().then(function (registrations) {
                            for (let registration of registrations) {
                                registration.unregister();
                            }
                        });
                        this.errors = {};
                        this.notification_fcm_json_file = "";
                        this.$refs.fileProperty.value = null;
                    }).catch((err) => {
                         this.loading.isActive = false;
                        if (err.response.data.status !== "undefined" && err.response.data.status === false) {
                            alertService.error(err.response.data.message)
                        } else {
                            this.errors = err.response.data.errors;
                        }

                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
        sendTestPush: async function () {
            if (!('Notification' in window)) {
                alertService.error('This browser does not support notifications.');
                return;
            }
            if (Notification.permission === 'denied') {
                alertService.error('Notifications are blocked. Please allow notifications for this site in your browser settings.');
                return;
            }
            if (Notification.permission === 'default') {
                try {
                    const permission = await Notification.requestPermission();
                    if (permission !== 'granted') {
                        alertService.error('Notification permission was not granted.');
                        return;
                    }
                } catch (e) {
                    alertService.error('Failed to request notification permission.');
                    return;
                }
            }
            
            this.loading.isActive = true;
            
            // Send test notification via backend (will trigger actual push notification)
            this.$store.dispatch('notification/testPush').then((res) => {
                this.loading.isActive = false;
                alertService.success(res.data.message || 'Test notification sent.');
                
                // Also show local notification to confirm browser permission works
                if (navigator.serviceWorker) {
                    navigator.serviceWorker.ready.then((reg) => {
                        reg.showNotification('Test Notification (Local)', { 
                            body: 'Your browser notification permission is working.', 
                            icon: '/images/default/firebase-logo.png',
                            badge: '/images/default/firebase-logo.png',
                            requireInteraction: true,
                            tag: 'test-notification'
                        }).catch((err) => {
                            console.error('Local test notification error:', err);
                        });
                    }).catch((err) => {
                        console.error('Service worker not ready:', err);
                    });
                }
            }).catch((err) => {
                this.loading.isActive = false;
                const msg = err?.response?.data?.message || err?.message || 'Failed to send test notification.';
                if (msg.includes('No device token') || msg.includes('device token')) {
                    alertService.error('Enable notifications first: Click your profile (top-right) → "Enable notifications", then try again.');
                } else {
                    alertService.error(msg);
                }
            });
        },
    },
};
</script>