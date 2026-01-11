<template>
    <div class="db-breadcrumb">
        <ul class="db-breadcrumb-list">
            <li v-if="Object.keys(authDefaultPermission).length > 0" class="db-breadcrumb-item">
                <router-link class="db-breadcrumb-link" :to="'/admin/'+authDefaultPermission.url">
                    {{ $t('menu.'+authDefaultPermission.name) }}
                </router-link>
            </li>
            <li class="db-breadcrumb-item" v-for="(val, key) of breadcrumbs">
                <span v-if="key !== Object.keys(breadcrumbs).length - 1">
                    <router-link class="db-breadcrumb-link" :to="val.path">
                        {{ $t('menu.'+val.meta.breadcrumb) }}
                    </router-link>
                </span>
                <span v-else>
                    {{ $t('menu.'+val.meta.breadcrumb) }}
                </span>
            </li>
        </ul>
        <button
            v-if="showRefreshButton"
            @click="refreshPage"
            type="button"
            class="ml-4 w-9 h-9 rounded-lg flex items-center justify-center bg-[#E8F4FD] hover:bg-[#D0E9FC] transition-colors"
            :title="$t('button.refresh') || 'Refresh'">
            <i class="lab lab-refresh-line lab-font-size-16 text-[#1776FF]"></i>
        </button>
    </div>
</template>

<script>
export default {
    name: "BreadcrumbComponent",
    data() {
        return {
            breadcrumbs: []
        }
    },
    computed: {
        authDefaultPermission: function () {
            return this.$store.getters.authDefaultPermission;
        },
        showRefreshButton: function () {
            // Show refresh button only on POS Orders and Table Orders pages
            return this.$route.name === 'admin.pos.orders.list' || 
                   this.$route.name === 'admin.table.order.list';
        }
    },
    watch: {
        $route() {
            this.route();
        }
    },
    created() {
        this.route();
    },
    methods: {
        route: function () {
            let i, routeArray = [], filterBreadCrumbs = this.$route.matched;
            if (filterBreadCrumbs.length > 0) {
                for (i = 0; i < filterBreadCrumbs.length; i++) {
                    if (filterBreadCrumbs[i].meta.breadcrumb) {
                        routeArray[i] = filterBreadCrumbs[i];
                    }
                }
            }
            this.breadcrumbs = routeArray;
        },
        refreshPage: function () {
            // Refresh current route using Vue Router (fast, no full page reload)
            this.$router.replace({
                name: this.$route.name,
                params: this.$route.params,
                query: { ...this.$route.query, _t: Date.now() }
            });
        }
    }
}
</script>

<style scoped>

</style>
