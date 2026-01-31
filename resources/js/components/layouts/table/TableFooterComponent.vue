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
                        <a href="#" @click.prevent="openCampaignModal" class="text-sm capitalize text-gray-600 hover:text-primary transition-colors">
                            {{ $t('button.join_campaign') }}
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

    <!-- Join Campaign Modal -->
    <div ref="campaignModal" id="campaign-modal" class="modal ff-modal">
        <div class="modal-dialog max-w-[400px] relative">
            <button class="modal-close fa-regular fa-circle-xmark absolute top-5 right-5"
                @click.prevent="closeCampaignModal"></button>
            <div class="modal-body">
                <h3 class="capitalize text-lg font-medium text-center mt-2 mb-4">
                    {{ $t('button.join_campaign') }}
                </h3>

                <!-- Phone Number Form -->
                <div>
                    <form @submit.prevent="checkUserCampaign" class="space-y-4 mb-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">{{ $t('label.whatsapp_number') }}</label>
                            <div class="flex gap-2">
                                <select v-model="campaignForm.prefix" class="w-[100px] text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                                    <option value="+994">+994</option>
                                </select>
                                <input 
                                    v-model="campaignForm.number" 
                                    type="text" 
                                    placeholder="50xxxxxx" 
                                    class="flex-1 text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary"
                                    required
                                    maxlength="12"
                                />
                            </div>
                        </div>
                        <button type="submit" :disabled="campaignLoading.isActive" 
                            class="w-full rounded-3xl text-center font-medium leading-6 py-3 bg-primary text-white hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ campaignLoading.isActive ? $t('button.loading') : $t('button.search') }}
                        </button>
                    </form>
                </div>

                <!-- Campaigns List -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-3">{{ $t('label.available_campaigns') }}</h4>
                    <div v-if="campaigns.length > 0" class="space-y-2 max-h-[300px] overflow-y-auto">
                        <div 
                            v-for="campaign in campaigns" 
                            :key="campaign.id"
                            class="p-3 rounded-lg border border-gray-200 hover:border-primary transition-colors"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-sm font-semibold text-gray-900 truncate">{{ campaign.name }}</h5>
                                    <p v-if="campaign.description" class="text-xs text-gray-500 truncate">{{ campaign.description }}</p>
                                    <!-- Campaign dates -->
                                    <p class="text-xs text-gray-400 mt-1">
                                        <span v-if="campaign.start_date">{{ formatCampaignDate(campaign.start_date) }}</span>
                                        <span v-if="campaign.start_date && campaign.end_date"> - </span>
                                        <span v-if="campaign.end_date">{{ formatCampaignDate(campaign.end_date) }}</span>
                                        <span v-if="!campaign.start_date && !campaign.end_date">{{ $t('label.ongoing') }}</span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span 
                                        class="text-xs px-2 py-0.5 rounded-full whitespace-nowrap"
                                        :class="campaign.type_name === 'percentage' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'"
                                    >
                                        {{ campaign.type_name === 'percentage' ? campaign.discount_value + '%' : $t('label.buy_x_get_free', { count: campaign.required_purchases }) }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="toggleCampaignInfo(campaign.id)"
                                        class="text-xs px-2 py-0.5 rounded-full border border-gray-200 text-gray-600 hover:border-primary hover:text-primary transition-colors"
                                    >
                                        {{ expandedCampaignId === campaign.id ? $t('button.hide') : $t('button.info') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Campaign Info -->
                            <div v-if="expandedCampaignId === campaign.id" class="mt-3 text-xs text-gray-600 space-y-1">
                                <div v-if="campaign.description" class="text-gray-700">
                                    <span class="font-medium">{{ $t('label.description') }}:</span>
                                    <span class="break-words">{{ campaign.description }}</span>
                                </div>
                                <div v-if="campaign.type_name === 'percentage'">
                                    <span class="font-medium">{{ $t('label.discount') }}:</span>
                                    {{ campaign.discount_value }}%
                                </div>
                                <div v-else>
                                    <div>
                                        <span class="font-medium">{{ $t('label.required_purchases') }}:</span>
                                        {{ campaign.required_purchases }}
                                    </div>
                                    <div v-if="campaign.free_item && campaign.free_item.name">
                                        <span class="font-medium">{{ $t('label.free_item') }}:</span>
                                        {{ campaign.free_item.name }}
                                    </div>
                                    <div v-else class="text-gray-500 italic">
                                        {{ $t('message.approach_branch') }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <!-- Show message to search first if not searched yet -->
                                <span 
                                    v-if="!hasSearchedCampaign"
                                    class="text-xs text-gray-400 italic"
                                >
                                    {{ $t('message.enter_phone_to_join') }}
                                </span>

                                <!-- After search: Show joined status or action buttons -->
                                <template v-else>
                                    <!-- User has joined THIS campaign -->
                                    <span 
                                        v-if="userCampaignId && userCampaignId === campaign.id"
                                        class="text-xs px-3 py-1 rounded-full bg-green-100 text-green-700 font-medium"
                                    >
                                        ✓ {{ $t('label.joined') }}
                                    </span>

                                    <!-- For percentage campaigns: show approach branch message if not joined -->
                                    <span 
                                        v-else-if="campaign.type_name === 'percentage'"
                                        class="text-xs text-gray-500 italic"
                                    >
                                        {{ $t('message.approach_branch') }}
                                    </span>

                                    <!-- For item campaigns: show Join button if not joined -->
                                    <button 
                                        v-else-if="campaign.type_name === 'item'"
                                        type="button"
                                        @click="joinCampaign(campaign)"
                                        :disabled="campaignLoading.isActive"
                                        class="text-xs px-3 py-1 rounded-full bg-primary text-white hover:bg-primary-dark transition-colors disabled:opacity-50"
                                    >
                                        {{ $t('button.join') }}
                                    </button>

                                    <!-- Status button: show for joined campaign (item type only) -->
                                    <button 
                                        v-if="userCampaignId && userCampaignId === campaign.id && campaign.type_name === 'item'"
                                        type="button"
                                        @click="viewCampaignStatus(campaign)"
                                        class="text-xs px-3 py-1 rounded-full border border-primary text-primary hover:bg-primary hover:text-white transition-colors"
                                    >
                                        {{ $t('button.status') }}
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-4">
                        <p class="text-sm text-gray-600">{{ $t('message.no_campaigns_available') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Progress Modal (separate overlay) -->
    <div v-if="showProgressModal && campaignProgress" class="fixed inset-0 z-[9999] flex items-center justify-center">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="closeProgressModal"></div>
        <!-- Modal Content -->
        <div class="relative bg-white rounded-lg shadow-xl max-w-[350px] w-full mx-4 p-6">
            <button 
                type="button"
                @click="closeProgressModal"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-xl"
            >
                ✕
            </button>
            
            <div class="text-center mb-4">
                <h4 class="text-lg font-semibold text-gray-900">
                    {{ campaignProgress.is_completed ? $t('label.campaign_completed') : $t('label.campaign_progress') }}
                </h4>
                <p class="text-sm text-gray-600 mt-1">{{ campaignProgress.campaign_name }}</p>
            </div>

            <!-- Campaign Completed Message -->
            <div v-if="campaignProgress.is_completed" class="mt-4 text-center">
                <div class="mb-4">
                    <div class="w-20 h-20 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-3">
                        <span class="text-4xl">🎉</span>
                    </div>
                    <p class="text-lg font-semibold text-green-600 mb-2">
                        {{ $t('message.campaign_completed') }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ $t('message.campaign_completed_description') }}
                    </p>
                    <p v-if="campaignProgress.free_item" class="text-sm text-gray-700 mt-3 font-medium">
                        {{ $t('message.you_received_free_item', { item: campaignProgress.free_item.name }) }}
                    </p>
                </div>
            </div>

            <!-- For percentage campaigns -->
            <p v-else-if="campaignProgress.type === 'percentage'" class="text-sm text-gray-600 text-center">
                {{ $t('message.approach_branch_for_discount') }}
            </p>

            <!-- Progress Circles (for active item type campaigns) -->
            <div v-else-if="campaignProgress.type === 'item' && !campaignProgress.is_completed" class="mt-4">
                <div class="flex justify-center flex-wrap gap-2 mb-4">
                    <div 
                        v-for="n in campaignProgress.required_purchases" 
                        :key="n"
                        class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-sm font-medium transition-all"
                        :class="n <= campaignProgress.current_progress ? 'bg-primary border-primary text-white' : 'border-gray-300 text-gray-400'"
                    >
                        <span v-if="n <= campaignProgress.current_progress">✓</span>
                        <span v-else>{{ n }}</span>
                    </div>
                </div>
                <p class="text-center text-sm text-gray-600">
                    {{ campaignProgress.current_progress }} / {{ campaignProgress.required_purchases }} {{ $t('label.orders') }}
                </p>
                <p v-if="campaignProgress.is_complete" class="text-center text-sm text-green-600 font-medium mt-2">
                    🎉 {{ $t('message.campaign_reward_ready') }}
                </p>
                <p v-if="campaignProgress.rewards_available > 0" class="text-center text-sm text-green-600 mt-1">
                    {{ $t('label.rewards_available') }}: {{ campaignProgress.rewards_available }}
                </p>
            </div>

            <button 
                type="button" 
                @click="closeProgressModal" 
                class="w-full mt-6 rounded-3xl text-center font-medium leading-6 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
            >
                {{ $t('button.close') }}
            </button>
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
            // Campaign data
            campaigns: [],
            campaignForm: {
                prefix: '+994',
                number: ''
            },
            campaignLoading: {
                isActive: false,
            },
            campaignProgress: null,
            showProgressModal: false, // Controls progress modal visibility
            userCampaignId: null, // Campaign the user has joined
            hasSearchedCampaign: false, // Whether user has clicked Search
            expandedCampaignId: null, // Campaign card info toggle
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        pages: function () {
            return this.$store.getters['frontendPage/lists'];
        },
        installInstructions: function () {
            return this.getInstallInstructions();
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
        getDeferredPrompt: function () {
            // Prefer local, fallback to global (captured early during app boot).
            return this.deferredPrompt || window.__deferredPwaPrompt || null;
        },
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
        formatCampaignDate: function (dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString(this.$i18n.locale || 'en', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        },
        toggleCampaignInfo: function (campaignId) {
            this.expandedCampaignId = (this.expandedCampaignId === campaignId) ? null : campaignId;
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
            const promptEvent = this.getDeferredPrompt();
            if (promptEvent) {
                // Keep a local reference too (useful if component logic relies on it)
                this.deferredPrompt = promptEvent;
                // Show the install prompt - this starts the installation process
                promptEvent.prompt();
                
                // Wait for the user to respond to the prompt
                promptEvent.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    // Clear the deferred prompt so it can only be used once
                    this.deferredPrompt = null;
                    window.__deferredPwaPrompt = null;
                }).catch((error) => {
                    console.error('Error showing install prompt:', error);
                    this.deferredPrompt = null;
                    window.__deferredPwaPrompt = null;
                });
            } else {
                // Fallback for mobile browsers (notably iOS) where beforeinstallprompt never fires.
                this.openInstallModal();
            }
        },
        // Campaign methods
        openCampaignModal: function () {
            this.campaignForm.prefix = '+994';
            this.campaignForm.number = '';
            this.campaignProgress = null;
            this.showProgressModal = false;
            this.userCampaignId = null;
            this.hasSearchedCampaign = false;
            this.loadCampaigns();
            
            const modalTarget = this.$refs.campaignModal;
            if (modalTarget) {
                modalTarget.classList.add("active");
                document.body.style.overflowY = "hidden";
            }
        },
        closeCampaignModal: function () {
            const modalTarget = this.$refs.campaignModal;
            if (modalTarget) {
                modalTarget.classList.remove("active");
                document.body.style.overflowY = "";
            }
            this.campaignProgress = null;
            this.showProgressModal = false;
        },
        loadCampaigns: function (phone = null, branchId = null) {
            this.campaignLoading.isActive = true;
            
            // Build query parameters
            const params = {};
            if (phone) {
                params.phone = phone;
            }
            if (branchId) {
                params.branch_id = branchId;
            }
            
            // Build URL with query string
            let url = 'frontend/campaign';
            if (Object.keys(params).length > 0) {
                const queryString = new URLSearchParams(params).toString();
                url += '?' + queryString;
            }
            
            axios.get(url).then((response) => {
                if (response.data.status && response.data.data) {
                    // Normalize numeric fields so strict equality checks work reliably
                    this.campaigns = (response.data.data || []).map((c) => {
                        return {
                            ...c,
                            id: c?.id !== undefined && c?.id !== null ? Number(c.id) : c.id,
                            required_purchases: c?.required_purchases !== undefined && c?.required_purchases !== null
                                ? Number(c.required_purchases)
                                : c.required_purchases,
                        };
                    });
                } else {
                    this.campaigns = [];
                }
                this.campaignLoading.isActive = false;
            }).catch((error) => {
                console.error('Error loading campaigns:', error);
                this.campaigns = [];
                this.campaignLoading.isActive = false;
            });
        },
        loadCampaignProgress: function () {
            const phoneNumber = this.campaignForm.prefix + this.campaignForm.number;
            let normalizedNumber = phoneNumber;
            if (normalizedNumber.startsWith('+9940')) {
                normalizedNumber = '+994' + normalizedNumber.substring(5);
            }

            const branchId = Number(
                this.onlineBranchId
                || this.$route.params.branchId
                || (this.branches && this.branches.length > 0 ? this.branches[0].id : null)
            );
            if (!branchId) {
                console.error('No branch selected');
                return;
            }

            this.campaignLoading.isActive = true;
            axios.post('frontend/campaign/progress', {
                phone: normalizedNumber,
                branch_id: branchId
            }).then((response) => {
                if (response.data.status && response.data.data) {
                    this.campaignProgress = response.data.data;
                    // Set the user's joined campaign ID
                    if (response.data.data.campaign_id) {
                        this.userCampaignId = Number(response.data.data.campaign_id);
                    }
                } else {
                    this.campaignProgress = null;
                    this.userCampaignId = null;
                }
                this.campaignLoading.isActive = false;
            }).catch((error) => {
                console.error('Error loading campaign progress:', error);
                this.campaignProgress = null;
                this.userCampaignId = null;
                this.campaignLoading.isActive = false;
            });
        },
        checkUserCampaign: function () {
            const phoneNumber = this.campaignForm.prefix + this.campaignForm.number;
            if (!phoneNumber || phoneNumber.length < 10) return;

            let normalizedNumber = phoneNumber;
            if (normalizedNumber.startsWith('+9940')) {
                normalizedNumber = '+994' + normalizedNumber.substring(5);
            }

            // Get branch ID from multiple sources
            const branchId = Number(
                this.onlineBranchId
                || this.$route.params.branchId
                || (this.branches && this.branches.length > 0 ? this.branches[0].id : null)
            );
            
            if (!branchId) {
                console.error('No branch ID available');
                return;
            }

            this.campaignLoading.isActive = true;
            axios.post('frontend/campaign/progress', {
                phone: normalizedNumber,
                branch_id: branchId
            }).then((response) => {
                console.log('=== Campaign Search Response ===');
                console.log('Response data:', response.data);
                if (response.data.status && response.data.data && response.data.data.campaign_id) {
                    this.userCampaignId = Number(response.data.data.campaign_id);
                    console.log('User joined campaign ID:', this.userCampaignId);
                    console.log('Available campaigns:', this.campaigns.map(c => `ID: ${c.id} (${c.name})`).join(', '));
                } else {
                    this.userCampaignId = null;
                    console.log('No campaign found for this user');
                }
                this.hasSearchedCampaign = true;
                this.campaignLoading.isActive = false;
                
                // Reload campaigns to filter out completed ones
                this.loadCampaigns(normalizedNumber, branchId);
            }).catch((error) => {
                this.userCampaignId = null;
                this.hasSearchedCampaign = false;
                this.campaignLoading.isActive = false;

                const msg = error?.response?.data?.message
                    || (error?.response?.data?.errors ? JSON.stringify(error.response.data.errors) : null)
                    || 'Failed to check campaign status';
                console.error('Campaign progress error:', error?.response?.data || error);
                alert(msg);
            });
        },
        viewCampaignStatus: function (campaign) {
            const phoneNumber = this.campaignForm.prefix + this.campaignForm.number;
            let normalizedNumber = phoneNumber;
            if (normalizedNumber.startsWith('+9940')) {
                normalizedNumber = '+994' + normalizedNumber.substring(5);
            }

            const branchId = Number(
                this.onlineBranchId
                || this.$route.params.branchId
                || (this.branches && this.branches.length > 0 ? this.branches[0].id : null)
            );
            if (!branchId) return;

            this.campaignLoading.isActive = true;
            axios.post('frontend/campaign/progress', {
                phone: normalizedNumber,
                branch_id: branchId
            }).then((response) => {
                if (response.data.status && response.data.data) {
                    this.campaignProgress = response.data.data;
                    this.showProgressModal = true;
                }
                this.campaignLoading.isActive = false;
            }).catch((error) => {
                console.error('Error loading campaign progress:', error);
                this.campaignLoading.isActive = false;
                const msg = error?.response?.data?.message
                    || (error?.response?.data?.errors ? JSON.stringify(error.response.data.errors) : null)
                    || 'Failed to load campaign status';
                alert(msg);
            });
        },
        closeProgressModal: function () {
            this.showProgressModal = false;
        },
        joinCampaign: function (campaign) {
            const phoneNumber = this.campaignForm.prefix + this.campaignForm.number;
            let normalizedNumber = phoneNumber;
            if (normalizedNumber.startsWith('+9940')) {
                normalizedNumber = '+994' + normalizedNumber.substring(5);
            }

            if (!normalizedNumber || normalizedNumber.length < 10) {
                alert(this.$t('message.enter_phone_first'));
                return;
            }

            const branchId = Number(
                this.onlineBranchId
                || this.$route.params.branchId
                || (this.branches && this.branches.length > 0 ? this.branches[0].id : null)
            );
            if (!branchId) {
                console.error('No branch selected');
                return;
            }

            this.campaignLoading.isActive = true;
            axios.post('frontend/campaign/join', {
                campaign_id: campaign.id,
                phone: normalizedNumber,
                branch_id: branchId
            }).then((response) => {
                if (response.data.status) {
                    // Set joined campaign ID immediately
                    this.userCampaignId = Number(campaign.id);
                    this.campaignLoading.isActive = false;
                } else {
                    alert(response.data.message || 'Failed to join campaign');
                    this.campaignLoading.isActive = false;
                }
            }).catch((error) => {
                console.error('Error joining campaign:', error);
                alert(error.response?.data?.message || 'Failed to join campaign');
                this.campaignLoading.isActive = false;
            });
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

        // Sync from global prompt if it was captured before this component mounted.
        this.deferredPrompt = window.__deferredPwaPrompt || null;

        // Listen for app installed event
        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            window.__deferredPwaPrompt = null;
            this.showInstallButton = false;
            console.log('PWA was installed');
        });
    }
}
</script>