<template>
    <LoadingComponent :props="loading" />

    <footer class="bg-white border-t border-gray-300">
        <div class="container">
            <div class="flex flex-col md:flex-row items-center justify-between gap-5 py-8">
                <div class="flex flex-col gap-3">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        <p class="text-sm text-gray-700">{{ setting.site_copyright }}</p>
                        <div class="flex items-center gap-4">
                        <div v-if="hasSocialLinks" class="flex items-center gap-4">
                            <a v-if="setting.site_facebook_link" :href="setting.site_facebook_link" target="_blank" rel="noopener noreferrer"
                               class="text-gray-600 hover:text-primary transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a v-if="setting.site_instagram_link" :href="setting.site_instagram_link" target="_blank" rel="noopener noreferrer"
                               class="text-gray-600 hover:text-primary transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a v-if="setting.site_tiktok_link" :href="setting.site_tiktok_link" target="_blank" rel="noopener noreferrer"
                               class="text-gray-600 hover:text-primary transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z" />
                                    </svg>
                                </a>
                            </div>
                            <a v-if="selectedBranch && branchMapLink" :href="branchMapLink" target="_blank" rel="noopener noreferrer"
                               class="text-gray-600 hover:text-primary transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <p v-if="setting.site_our_message" class="text-sm text-gray-600 max-w-md">{{ setting.site_our_message }}</p>
                    <div class="flex flex-wrap items-center gap-4 gap-y-2">
                        <a href="#" @click.prevent="trackOrder" class="text-sm capitalize text-gray-600 hover:text-primary transition-colors">
                            {{ $t('button.track_order') }}
                        </a>
                        <a v-if="!isStandalone" href="#" @click.prevent="installApp" class="text-sm capitalize text-gray-600 hover:text-primary transition-colors">
                            {{ $t('button.install_app') }}
                        </a>
                    </div>
                </div>
                <nav v-if="pages.length > 0" class="flex flex-wrap items-center gap-6">
                    <router-link v-for="page in pages" :key="page.id" class="text-sm capitalize text-gray-700 hover:text-primary transition-colors"
                        :to="getPageRoute(page.slug)">
                        {{ page.title }}
                    </router-link>
                </nav>
            </div>
        </div>
    </footer>

    <!-- Track Order Modal -->
    <div ref="trackOrderModal" id="track-order-modal" class="modal ff-modal">
        <div class="modal-dialog max-w-[500px] relative">
            <button class="modal-close fa-regular fa-circle-xmark absolute top-5 right-5"
                @click.prevent="closeTrackModal"></button>
            <div class="modal-body">
                <h3 class="capitalize text-lg font-medium text-center mt-2 mb-4">
                    {{ $t('button.track_order') }}
                </h3>
                <form @submit.prevent="searchOrders" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">{{ $t('label.whatsapp_number') }}</label>
                        <div class="flex gap-2">
                            <select v-model="trackForm.prefix" class="w-[100px] text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                                <option value="+994">+994</option>
                            </select>
                            <input 
                                v-model="trackForm.number" 
                                type="text" 
                                placeholder="50xxxxxx" 
                                class="flex-1 text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary"
                                required
                                maxlength="12"
                            />
                        </div>
                    </div>
                    <button type="submit" :disabled="trackLoading.isActive" 
                        class="w-full rounded-3xl text-center font-medium leading-6 py-3 bg-primary text-white hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ trackLoading.isActive ? $t('button.loading') : $t('button.search') }}
                    </button>
                </form>

                <!-- Orders List -->
                <div v-if="trackOrders.length > 0" class="mt-6 space-y-3">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">{{ $t('label.orders') }}</h4>
                    <div class="space-y-2 max-h-[300px] overflow-y-auto">
                        <a 
                            v-for="order in trackOrders" 
                            :key="order.id"
                            :href="getOrderUrl(order)"
                            @click="closeTrackModal"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block p-3 rounded-lg border border-gray-200 hover:border-primary hover:bg-primary/5 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $t('label.order') }} #{{ order.order_serial_no }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ order.order_datetime }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-primary">{{ order.total_currency_price }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- No Orders Found -->
                <div v-if="trackOrders.length === 0 && trackSearched" class="mt-6 text-center py-4">
                    <p class="text-sm text-gray-600">{{ $t('message.no_orders_found') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- PWA Install Instructions Modal (fallback for mobile browsers without beforeinstallprompt) -->
    <div ref="installAppModal" id="install-app-modal" class="modal ff-modal">
        <div class="modal-dialog max-w-[500px] relative">
            <button class="modal-close fa-regular fa-circle-xmark absolute top-5 right-5"
                    @click.prevent="closeInstallModal"></button>
            <div class="modal-body">
                <h3 class="capitalize text-lg font-medium text-center mt-2 mb-4">
                    {{ $t('button.install_app') }}
                </h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">
                    {{ installInstructions }}
                </p>
                <button
                    type="button"
                    class="w-full mt-6 rounded-3xl text-center font-medium leading-6 py-3 bg-primary text-white hover:bg-primary-dark transition-colors"
                    @click.prevent="closeInstallModal"
                >
                    {{ $t('button.continue') }}
                </button>
            </div>
        </div>
    </div>
</template>


<script>
import axios from "axios";
import statusEnum from "../../../enums/modules/statusEnum";
import menuSectionEnum from "../../../enums/modules/menuSectionEnum";
import LoadingComponent from "../../frontend/components/LoadingComponent";

export default {
    name: "TableFooterComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            deferredPrompt: null,
            showInstallButton: false,
            trackForm: {
                prefix: '+994',
                number: ''
            },
            trackOrders: [],
            trackLoading: {
                isActive: false,
            },
            trackSearched: false,
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        pages: function () {
            return this.$store.getters['frontendPage/lists'];
        },
        isOnlineOrder: function () {
            return this.$route.name && this.$route.name.startsWith('online.');
        },
        hasSocialLinks: function () {
            return this.setting.site_facebook_link || this.setting.site_instagram_link || this.setting.site_tiktok_link;
        },
        isStandalone: function () {
            // Check if app is already installed (running in standalone mode)
            return window.matchMedia('(display-mode: standalone)').matches || 
                   window.navigator.standalone === true ||
                   document.referrer.includes('android-app://');
        },
        onlineBranchId: function () {
            return this.$store.getters['tableCart/onlineBranchId'];
        },
        branches: function () {
            return this.$store.getters['frontendBranch/lists'];
        },
        selectedBranch: function () {
            if (!this.onlineBranchId || !this.branches || this.branches.length === 0) {
                return null;
            }
            return this.branches.find(branch => branch.id === this.onlineBranchId) || null;
        },
        branchMapLink: function () {
            if (!this.selectedBranch) {
                return null;
            }
            const branch = this.selectedBranch;
            
            // If latitude and longitude are available, use them for more accurate location
            if (branch.latitude && branch.longitude) {
                return `https://www.google.com/maps?q=${branch.latitude},${branch.longitude}`;
            }
            
            // Otherwise, use the address
            if (branch.address) {
                return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(branch.address)}`;
            }
            
            return null;
        }
    },
    methods: {
        isIOSDevice: function () {
            // iOS Safari does not support the native beforeinstallprompt flow.
            // (Chrome on iOS is also Safari under the hood.)
            return /iphone|ipad|ipod/i.test(window.navigator.userAgent || '');
        },
        isAndroidDevice: function () {
            return /android/i.test(window.navigator.userAgent || '');
        },
        getInstallInstructions: function () {
            if (this.isIOSDevice()) {
                return this.$t('message.pwa_install_ios');
            }
            if (this.isAndroidDevice()) {
                return this.$t('message.pwa_install_android');
            }
            return this.$t('message.pwa_install_instructions');
        },
        openInstallModal: function () {
            const modalTarget = this.$refs.installAppModal;
            if (modalTarget) {
                modalTarget.classList.add("active");
                document.body.style.overflowY = "hidden";
            }
        },
        closeInstallModal: function () {
            const modalTarget = this.$refs.installAppModal;
            if (modalTarget) {
                modalTarget.classList.remove("active");
                document.body.style.overflowY = "";
            }
        },
        getPageRoute: function (pageSlug) {
            if (this.isOnlineOrder) {
                return { 
                    name: 'online.page', 
                    params: { 
                        branchId: this.$route.params.branchId, 
                        pageSlug: pageSlug 
                    } 
                };
            }
            return { 
                name: 'table.page', 
                params: { 
                    slug: this.$route.params.slug, 
                    pageSlug: pageSlug 
                } 
            };
        },
        trackOrder: function () {
            // Reset form and results
            this.trackForm.prefix = '+994';
            this.trackForm.number = '';
            this.trackOrders = [];
            this.trackSearched = false;
            
            // Show modal
            const modalTarget = this.$refs.trackOrderModal;
            if (modalTarget) {
                modalTarget.classList.add("active");
                document.body.style.overflowY = "hidden";
            }
        },
        closeTrackModal: function () {
            const modalTarget = this.$refs.trackOrderModal;
            if (modalTarget) {
                modalTarget.classList.remove("active");
                document.body.style.overflowY = "";
            }
            this.trackOrders = [];
            this.trackSearched = false;
        },
        searchOrders: function () {
            const phoneNumber = this.trackForm.prefix + this.trackForm.number;
            
            // Normalize: if number starts with 0, remove it (handles +9940503531437)
            let normalizedNumber = phoneNumber;
            if (normalizedNumber.startsWith('+9940')) {
                normalizedNumber = '+994' + normalizedNumber.substring(5);
            }
            
            this.trackLoading.isActive = true;
            this.trackSearched = false;
            
            // Debug: log the request URL
            console.log('Requesting URL:', axios.defaults.baseURL + '/table/dining-order/track?phone_number=' + normalizedNumber);
            
            // Fetch orders from API (axios already has /api in baseURL)
            axios.get('table/dining-order/track', {
                params: {
                    phone_number: normalizedNumber
                }
            }).then((response) => {
                console.log('Track order API response:', response.data);
                if (response.data.status && response.data.data) {
                    this.trackOrders = response.data.data;
                    if (response.data.debug) {
                        console.log('Debug info:', response.data.debug);
                    }
                } else {
                    this.trackOrders = [];
                }
                this.trackSearched = true;
                this.trackLoading.isActive = false;
            }).catch((error) => {
                console.error('Error searching orders:', error);
                if (error.response) {
                    console.error('Error response:', error.response.data);
                }
                this.trackOrders = [];
                this.trackSearched = true;
                this.trackLoading.isActive = false;
            });
        },
        getOrderUrl: function (order) {
            if (order.is_table_order && order.dining_table_slug) {
                return `/table-order/${order.dining_table_slug}/${order.id}`;
            } else {
                return `/online/order/${order.branch_id}/${order.id}`;
            }
        },
        installApp: function () {
            if (this.deferredPrompt) {
                // Show the install prompt - this starts the installation process
                this.deferredPrompt.prompt();
                
                // Wait for the user to respond to the prompt
                this.deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    // Clear the deferred prompt so it can only be used once
                    this.deferredPrompt = null;
                }).catch((error) => {
                    console.error('Error showing install prompt:', error);
                    this.deferredPrompt = null;
                });
            } else {
                // Fallback for mobile browsers (notably iOS) where beforeinstallprompt never fires.
                this.openInstallModal();
            }
        }
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch("frontendPage/lists", {
            paginate: 0,
            order_column: "id",
            order_type: "asc",
            menu_section_id: menuSectionEnum.FOOTER,
            status: statusEnum.ACTIVE
        }).then(res => {
            this.loading.isActive = false;
        }).catch((err) => {
            this.loading.isActive = false;
        });

        // Listen for the beforeinstallprompt event (PWA install prompt)
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later
            this.deferredPrompt = e;
            // Show the install button
            this.showInstallButton = true;
        });

        // Listen for app installed event
        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            this.showInstallButton = false;
            console.log('PWA was installed');
        });
    }
}
</script>