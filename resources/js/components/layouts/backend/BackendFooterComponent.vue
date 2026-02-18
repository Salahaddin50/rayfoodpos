<template>
    <footer class="bg-white border-t border-gray-300">
        <div class="container py-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm text-gray-700">{{ setting.site_copyright }}</p>
                <a v-if="!isStandalone" href="#" @click.prevent="installApp"
                    class="text-sm capitalize text-gray-600 hover:text-primary transition-colors">
                    {{ $t('button.install_app') }}
                </a>
            </div>
        </div>
    </footer>

    <!-- PWA Install Instructions Modal -->
    <div ref="installAppModal" id="install-admin-app-modal" class="modal ff-modal">
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
export default {
    name: "BackendFooterComponent",
    data() {
        return {
            deferredPrompt: null,
        };
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        installInstructions: function () {
            return this.getInstallInstructions();
        },
        isStandalone: function () {
            return window.matchMedia('(display-mode: standalone)').matches ||
                   window.navigator.standalone === true ||
                   document.referrer.includes('android-app://');
        },
    },
    methods: {
        getDeferredPrompt: function () {
            return this.deferredPrompt || window.__deferredPwaPrompt || null;
        },
        isIOSDevice: function () {
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
        installApp: function () {
            const promptEvent = this.getDeferredPrompt();
            if (promptEvent) {
                this.deferredPrompt = promptEvent;
                promptEvent.prompt();
                promptEvent.userChoice.then((choiceResult) => {
                    this.deferredPrompt = null;
                    window.__deferredPwaPrompt = null;
                }).catch(() => {
                    this.deferredPrompt = null;
                    window.__deferredPwaPrompt = null;
                });
            } else {
                this.openInstallModal();
            }
        },
    },
    mounted() {
        this.deferredPrompt = window.__deferredPwaPrompt || null;
        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            window.__deferredPwaPrompt = null;
        });
    },
};
</script>
