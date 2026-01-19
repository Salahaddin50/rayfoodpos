<template>
    <section class="mb-16 mt-8">
        <div class="container">
            <LoadingComponent :props="loading" />

            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-primary mb-2">{{ $t('label.online_order') }}</h1>
                <p class="text-gray-600">{{ $t('message.select_branch_to_order') }}</p>
            </div>

            <div v-if="branches.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="branch in branches" :key="branch.id"
                    class="bg-white rounded-2xl shadow-xs p-6 hover:shadow-md transition cursor-pointer"
                    @click="selectBranch(branch.id)">
                    <h3 class="text-xl font-semibold text-heading mb-3">{{ branch.name }}</h3>
                    <div class="flex flex-col gap-2 text-sm text-gray-600">
                        <div class="flex items-start gap-2">
                            <i class="lab lab-location-marker text-primary mt-0.5"></i>
                            <span>{{ branch.address }}</span>
                        </div>
                        <div v-if="branch.phone" class="flex items-center gap-2">
                            <i class="lab lab-phone text-primary"></i>
                            <span>{{ branch.phone }}</span>
                        </div>
                        <div v-if="branch.email" class="flex items-center gap-2">
                            <i class="lab lab-mail text-primary"></i>
                            <span>{{ branch.email }}</span>
                        </div>
                    </div>
                    <button type="button"
                        class="w-full mt-4 rounded-3xl capitalize font-medium leading-6 py-2.5 text-white bg-primary hover:bg-primary-dark transition">
                        {{ $t('button.order_now') }}
                    </button>
                </div>
            </div>

            <div v-else class="mt-12">
                <div class="max-w-[250px] mx-auto">
                    <img class="w-full mb-8" :src="setting.image_order_not_found" alt="no_branches">
                </div>
                <p class="text-center text-gray-600">{{ $t('message.no_branches_available') }}</p>
            </div>
        </div>
    </section>
</template>

<script>
import LoadingComponent from "../table/components/LoadingComponent.vue";
import statusEnum from "../../enums/modules/statusEnum";

export default {
    name: "OnlineBranchSelectionComponent",
    components: {
        LoadingComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            branchProps: {
                search: {
                    paginate: 0,
                    order_column: "id",
                    order_type: "asc",
                    status: statusEnum.ACTIVE
                },
            },
        }
    },
    computed: {
        branches: function () {
            return this.$store.getters["frontendBranch/lists"];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch("frontendBranch/lists", this.branchProps.search).then(res => {
            this.loading.isActive = false;
            
            // Auto-select first branch after 2 seconds
            if (this.branches && this.branches.length > 0) {
                setTimeout(() => {
                    const firstBranch = this.branches[0];
                    if (firstBranch && firstBranch.id) {
                        this.selectBranch(firstBranch.id);
                    }
                }, 2000);
            }
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },
    methods: {
        selectBranch: function (branchId) {
            // Store selected branch in cart
            this.$store.dispatch('tableCart/initOnlineBranch', branchId).then().catch();
            this.$router.push({ name: 'online.menu', params: { branchId: branchId } });
        }
    }
}
</script>

