<template>
    <LoadingComponent :props="loading" />
    <section class="pt-8 pb-16">
        <div class="container max-w-[360px] py-6 p-4 mb-6 sm:px-6 shadow-xs rounded-2xl bg-white">
            <h2 class="capitalize mb-6 text-center text-[22px] font-semibold leading-[34px] text-heading">
                {{ $t('label.welcome_back') }}
            </h2>
            <div v-if="errors.validation"
                class="bg-red-100 border border-red-400 text-red-700 px-3 py-3 mb-5 rounded relative flex items-start gap-2"
                role="alert">
                <span class="block sm:inline text-sm flex-auto">{{ errors.validation }}</span>
                <button type="button" @click="close" class="leading-none">
                    <i class="lab lab-close-circle-line"></i>
                </button>
            </div>
            <form @submit.prevent="login">
                <div class="mb-4">
                    <label for="formEmail" class="text-sm capitalize mb-1 text-heading">{{ $t('label.email') }}</label>
                    <input type="text" :class="errors.email ? 'invalid' : ''" v-model="form.email"
                        class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]" id="formEmail">
                    <small class="db-field-alert" v-if="errors.email">{{ errors.email[0] }}</small>
                </div>
                <div class="mb-4">
                    <label for="formPassword" class="text-sm capitalize mb-1 text-heading">{{
                        $t('label.password')
                        }}</label>
                    <input autocomplete="off" type="password" :class="errors.password ? 'invalid' : ''"
                        v-model="form.password" class="w-full h-12 rounded-lg border px-4 border-[#D9DBE9]"
                        id="formPassword">
                    <small class="db-field-alert" v-if="errors.password">{{ errors.password[0] }}</small>
                </div>

                <div v-if="turnstile.enabled" class="mb-4">
                    <div v-if="turnstileLoading" class="flex items-center justify-center h-[65px] bg-gray-50 rounded border border-gray-200">
                        <span class="text-sm text-gray-500">{{ $t('label.loading_captcha') || 'Loading security check...' }}</span>
                    </div>
                    <div ref="turnstileEl" :class="{ 'hidden': turnstileLoading }"></div>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <div class="db-field-checkbox p-0">
                        <div class="custom-checkbox w-3 h-3">
                            <input type="checkbox" id="checkbox2" class="custom-checkbox-field">
                            <i
                                class="fa-solid fa-check custom-checkbox-icon leading-[9px] text-[9px] rounded-[3px] border-[#6E7191]"></i>
                        </div>
                        <label for="checkbox2" class="db-field-label text-xs text-heading">
                            {{ $t('label.remember_me') }}
                        </label>
                    </div>
                    <router-link :to="{ name: 'auth.forgetPassword' }"
                        class="capitalize text-xs font-medium transition text-primary">
                        {{ $t('button.forget_password') }}
                    </router-link>
                </div>
                <button type="submit"
                    class="w-full h-12 text-center capitalize font-medium rounded-3xl mb-6 text-white bg-primary">
                    {{ $t('button.login') }}
                </button>
            </form>
        </div>

        <div v-if="demo === 'true' || demo === 'TRUE' || demo === 'True' || demo === '1' || demo === 1"
            class="container max-w-[360px] py-6 p-4 sm:px-6 shadow-xs rounded-2xl bg-white">
            <h2 class="mb-6 text-center text-lg font-medium text-heading">{{ $t('message.for_quick_demo') }}</h2>
            <nav class="grid grid-cols-2 gap-3">
                <button @click.prevent="setupCredit('admin')"
                    class="click-to-prop w-full h-10 leading-10 rounded-lg text-center text-sm capitalize text-white bg-orange-500"
                    id="adminClick">
                    {{ $t('label.admin') }}
                </button>
                <button @click.prevent="setupCredit('branchManager')"
                    class="click-to-prop w-full h-10 leading-10 rounded-lg text-center text-sm capitalize text-white bg-sky-600"
                    id="branchManagerClick">
                    {{ $t('label.branch_manager') }}
                </button>
                <button @click.prevent="setupCredit('posOperator')"
                    class="click-to-prop w-full h-10 leading-10 rounded-lg text-center text-sm capitalize text-white bg-purple-500"
                    id="posOperatorClick">
                    {{ $t('label.pos_operator') }}
                </button>
                <button @click.prevent="setupCredit('chef')"
                    class="click-to-prop w-full h-10 leading-10 rounded-lg text-center text-sm capitalize text-white bg-green-500"
                    id="chefClick">
                    {{ $t('label.chef_kitchen') }}
                </button>
            </nav>
        </div>
    </section>
</template>

<script>
import router from "../../../router";
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import ENV from "../../../config/env";
import { routes } from "../../../router";
import appService from "../../../services/appService";

export default {
    name: "LoginComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            form: {
                email: "",
                password: "",
                cf_turnstile_response: ""
            },
            errors: {},
            permissions: {},
            firstMenu: null,
            demo: ENV.DEMO,
            turnstile: {
                enabled: ENV.TURNSTILE_ENABLED === 'true' || ENV.TURNSTILE_ENABLED === 'TRUE' || ENV.TURNSTILE_ENABLED === '1' || ENV.TURNSTILE_ENABLED === 1,
                siteKey: ENV.TURNSTILE_SITE_KEY || '',
            },
            turnstileWidgetId: null,
            turnstileLoading: true,
        }
    },
    mounted() {
        if (this.turnstile.enabled && this.turnstile.siteKey) {
            this.initTurnstile();
        }
    },
    computed: {
        permission: function () {
            return this.$store.getters.authPermission;
        }
    },
    methods: {
        loadTurnstileScript() {
            return new Promise((resolve, reject) => {
                if (window.turnstile) {
                    resolve();
                    return;
                }

                const existing = document.querySelector('script[data-cf-turnstile]');
                if (existing) {
                    existing.addEventListener('load', resolve, { once: true });
                    existing.addEventListener('error', reject, { once: true });
                    return;
                }

                const s = document.createElement('script');
                s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
                s.async = true;
                s.defer = true;
                s.setAttribute('data-cf-turnstile', '1');
                s.onload = () => resolve();
                s.onerror = (e) => reject(e);
                document.head.appendChild(s);
            });
        },
        initTurnstile() {
            this.loadTurnstileScript().then(() => {
                if (!this.$refs.turnstileEl || !window.turnstile) {
                    this.turnstileLoading = false;
                    return;
                }

                // Render once
                if (this.turnstileWidgetId !== null) return;

                this.turnstileWidgetId = window.turnstile.render(this.$refs.turnstileEl, {
                    sitekey: this.turnstile.siteKey,
                    callback: (token) => {
                        this.form.cf_turnstile_response = token;
                        this.turnstileLoading = false;
                    },
                    'expired-callback': () => {
                        this.form.cf_turnstile_response = '';
                    },
                    'error-callback': () => {
                        this.form.cf_turnstile_response = '';
                        this.turnstileLoading = false;
                    },
                    'after-interactive-callback': () => {
                        this.turnstileLoading = false;
                    },
                });
                
                // Fallback: hide loading after 3 seconds regardless
                setTimeout(() => {
                    this.turnstileLoading = false;
                }, 3000);
            }).catch(() => {
                // If Turnstile script can't load, fail open (let backend enforce if enabled)
                this.turnstileWidgetId = null;
                this.turnstileLoading = false;
            });
        },
        login: function () {
            try {
                if (this.turnstile.enabled && this.turnstile.siteKey && !this.form.cf_turnstile_response) {
                    this.errors = { validation: 'Please complete the captcha.' };
                    return;
                }

                this.loading.isActive = true;
                this.$store.dispatch('login', this.form).then((res) => {
                    this.loading.isActive = false;
                    alertService.success(res.data.message);
                    router.push({ name: "admin.dashboard" });

                    setTimeout(() => {
                        appService.recursiveRouter(routes, this.permission);
                    }, 300)

                }).catch((err) => {
                    this.loading.isActive = false;
                    const data = err?.response?.data;
                    const fallbackMessage = data?.message || err?.message || this.$t('message.something_wrong');
                    // Always keep errors as an object so template bindings like `errors.validation` never crash
                    this.errors = data?.errors ? data.errors : { validation: fallbackMessage };

                    // Reset captcha on failed attempts
                    if (this.turnstileWidgetId !== null && window.turnstile) {
                        try {
                            window.turnstile.reset(this.turnstileWidgetId);
                        } catch (e) {
                            // ignore
                        }
                        this.form.cf_turnstile_response = '';
                    }
                })
            } catch (err) {
                this.loading.isActive = false;
                this.errors = { validation: err?.message || this.$t('message.something_wrong') };
            }
        },
        close: function () {
            this.errors = {}
        },
        setupCredit: function (e) {
            if (e === 'admin') {
                this.form.email = 'admin@example.com';
                this.form.password = '123456';
            } else if (e === 'customer') {
                this.form.email = 'customer@example.com';
                this.form.password = '123456';
            } else if (e === 'branchManager') {
                this.form.email = 'branchmanager@example.com';
                this.form.password = '123456';
            } else if (e === 'posOperator') {
                this.form.email = 'posoperator@example.com';
                this.form.password = '123456';
            } else if (e === 'chef') {
                this.form.email = 'chef@example.com';
                this.form.password = '123456';
            }
        }
    }
}
</script>