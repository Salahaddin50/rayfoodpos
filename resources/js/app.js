/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import {createApp} from 'vue';
import DefaultComponent from "./components/DefaultComponent.vue";
import router from './router';
import store from './store';
import axios from 'axios';
import i18n from "./i18n";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import VueSimpleAlert from "vue3-simple-alert";
import VueNextSelect from 'vue-next-select';
import 'vue-next-select/dist/index.css';
import ENV from './config/env';
import 'sweetalert2/dist/sweetalert2.min.css';

// PWA install prompt handling (Android/Chrome)
// Store the install prompt event globally so it doesn't get lost across route/layout changes.
window.__deferredPwaPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();
    window.__deferredPwaPrompt = e;
});
window.addEventListener('appinstalled', () => {
    window.__deferredPwaPrompt = null;
});

/* Start tooltip alert code */
const options = {
    timeout: 2000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false
};
/* End tooltip alert code */


/* Start axios code*/
const API_URL = ENV.API_URL;
const API_KEY = ENV.API_KEY;

axios.defaults.baseURL = API_URL + '/api';

const persistedVuexState = () => {
    try {
        const state = localStorage.getItem('vuex');
        return state ? JSON.parse(state) : {};
    } catch (error) {
        return {};
    }
};

const currentAuthToken = () => {
    return store.getters.authToken || persistedVuexState()?.auth?.authToken || null;
};

axios.interceptors.request.use(
    config => {
        config.headers['x-api-key'] = API_KEY;
        const vuex = persistedVuexState();
        const token = store.getters.authToken || vuex?.auth?.authToken;
        const language = vuex?.globalState?.lists?.language_code;

        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        } else {
            delete config.headers['Authorization'];
        }

        if (language) {
            config.headers['x-localization'] = language;
        }

        return config;
    },
    error => Promise.reject(error),
);

// Response interceptor to handle 401 errors
let isRedirecting = false;
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            const currentPath = window.location.pathname;
            const isAdminArea = currentPath.startsWith('/admin');
            const token = currentAuthToken();
            const requestConfig = error.config;
            const sentAuthorization = requestConfig?.headers?.Authorization
                || requestConfig?.headers?.authorization;
            const responseMessage = error.response?.data?.message
                || error.response?.data?.errors;
            const isUnauthenticated = responseMessage === 'Unauthenticated.'
                || responseMessage === 'unauthenticated';

            // A Chromium request can occasionally leave before persisted auth is
            // available. Retry it once with the active Vuex token instead of
            // treating that request as a logout.
            console.log('[AUTH DEBUG]', {
                isAdminArea,
                hasConfig: !!requestConfig,
                token: token ? 'present' : 'missing',
                sentAuth: sentAuthorization ? 'present' : 'missing',
                hasRetry: !!requestConfig?._authRetry,
                url: requestConfig?.url,
                willRetry: isAdminArea && requestConfig && token && !sentAuthorization && !requestConfig._authRetry
            });
            if (isAdminArea && requestConfig && token && !sentAuthorization && !requestConfig._authRetry) {
                console.log('[AUTH RETRY] Retrying request with token');
                requestConfig._authRetry = true;
                requestConfig.headers = requestConfig.headers || {};
                requestConfig.headers['Authorization'] = `Bearer ${token}`;
                return axios(requestConfig);
            }

            // Only force redirect to admin login for admin area pages.
            // Public pages (e.g., /online/*, /table-order/*) should NOT be redirected to /admin/login.
            console.log('[AUTH LOGOUT CHECK]', {
                isAdminArea,
                isUnauthenticated,
                responseMessage,
                willLogout: isAdminArea && isUnauthenticated
            });
            if (isAdminArea && isUnauthenticated) {
                // Prevent multiple redirects
                if (isRedirecting) {
                    return Promise.reject(error);
                }

                // Clear full auth in memory + localStorage.
                // Clearing only authToken leaves authStatus=true and causes a login↔dashboard loop.
                try {
                    store.commit('authLogout');
                } catch (e) {
                    // ignore
                }
                try {
                    if (localStorage.getItem('vuex')) {
                        const vuex = JSON.parse(localStorage.getItem('vuex'));
                        if (vuex.auth) {
                            vuex.auth = {
                                ...vuex.auth,
                                authStatus: false,
                                authToken: null,
                                authBranchId: '',
                                authInfo: {},
                                authMenu: [],
                                authPermission: {},
                                authDefaultPermission: {},
                                authDefaultMenu: {},
                            };
                            localStorage.setItem('vuex', JSON.stringify(vuex));
                        }
                    }
                } catch (e) {
                    // ignore
                }

                // Redirect to login page (only if not already there)
                if (currentPath !== '/admin/login') {
                    isRedirecting = true;
                    window.location.href = '/admin/login';
                }
            }
        }
        return Promise.reject(error);
    }
);
/* End axios code */

const app = createApp(DefaultComponent);
app.component('vue-select', VueNextSelect)
app.use(router)
app.use(store)
app.use(VueSimpleAlert)
app.use(Toast, options)
app.use(i18n)
app.mount('#app');
