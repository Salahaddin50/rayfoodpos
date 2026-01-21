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
axios.interceptors.request.use(
    config => {
        config.headers['x-api-key'] = API_KEY;
        if (localStorage.getItem('vuex')) {
            const vuex = JSON.parse(localStorage.getItem('vuex'));
            const token = vuex.auth.authToken;
            const language = vuex.globalState.lists.language_code;
            config.headers['Authorization'] = token ? `Bearer ${token}` : '';
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
            // Prevent multiple redirects
            if (isRedirecting) {
                return Promise.reject(error);
            }
            
            // Clear auth token
            if (localStorage.getItem('vuex')) {
                const vuex = JSON.parse(localStorage.getItem('vuex'));
                if (vuex.auth) {
                    vuex.auth.authToken = null;
                    localStorage.setItem('vuex', JSON.stringify(vuex));
                }
            }
            
            // Redirect to login page (only if not already there)
            const currentPath = window.location.pathname;
            if (currentPath !== '/admin/login') {
                isRedirecting = true;
                window.location.href = '/admin/login';
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
