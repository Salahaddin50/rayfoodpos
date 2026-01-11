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
            <li v-if="showRefreshButton" class="db-breadcrumb-item db-breadcrumb-item-action">
                <button
                    @click="refreshPage"
                    type="button"
                    class="db-breadcrumb-link db-breadcrumb-link-action inline-flex items-center justify-center w-9 h-9 rounded-lg bg-[#E8F4FD] hover:bg-[#D0E9FC] transition-colors"
                    title="Refresh">
                    <i class="fa-solid fa-rotate-right text-[#1776FF] text-base opacity-100"></i>
                </button>
            </li>
        </ul>
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
            // Show refresh button only on POS Orders and Table Orders sections
            return this.$route.matched?.some((r) =>
                r?.meta?.breadcrumb === 'pos_orders' || r?.meta?.breadcrumb === 'table_orders'
            );
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
            // Add a temporary query param to trigger route update, then remove it to keep URL clean.
            const originalQuery = { ...this.$route.query };
            this.$router.replace({
                name: this.$route.name,
                params: this.$route.params,
                query: { ...originalQuery, _t: Date.now() }
            }).then(() => {
                this.$nextTick(() => {
                    this.$router.replace({
                        name: this.$route.name,
                        params: this.$route.params,
                        query: originalQuery
                    }).catch(() => { });
                });
            });
        }
    }
}
</script>

<style scoped>
/* Breadcrumb links add a "/" via ::after. Disable it for the refresh action. */
.db-breadcrumb-link-action::after {
    content: '' !important;
    padding: 0 !important;
}

/* Keep consistent spacing from the last breadcrumb item */
.db-breadcrumb-item-action {
    margin-left: 8px;
}
</style>
