<template>
    <aside class="db-sidebar">
        <div class="db-sidebar-header">
            <router-link class="w-24" :to="{ name: 'admin.dashboard' }">
                <img :src="setting.theme_logo" alt="logo">
            </router-link>
            <button @click.prevent="handleSidebar" class="fa-solid fa-xmark xmark-btn close-db-menu"></button>
        </div>
        <!--        {{ menus }}-->
        <nav class="db-sidebar-nav">
            <ul class="db-sidebar-nav-list" v-if="menus.length > 0" v-for="menu in menus" :key="menu">
                <li class="db-sidebar-nav-item" v-if="menu.url === '#'" @click.prevent="sidebarActive($event)">
                    <a href="javascript:void(0);" class="db-sidebar-nav-title">
                        {{ $t('menu.' + menu.language) }}
                    </a>
                </li>

                <li class="db-sidebar-nav-item" v-else @click.prevent="sidebarActive($event)">
                    <router-link :to="'/admin/' + menu.url" class="db-sidebar-nav-menu">
                        <i class="text-sm" :class="menu.icon"></i>
                        <span class="text-base flex-auto">{{ $t('menu.' + menu.language) }}</span>
                    </router-link>
                </li>

                <li class="db-sidebar-nav-item" v-if="menu.children" v-for="children in menu.children"
                    @click.prevent="sidebarActive($event)">
                    <router-link :to="'/admin/' + children.url" class="db-sidebar-nav-menu">
                        <i class="text-sm" :class="children.icon"></i>
                        <span class="text-base flex-auto">{{ $t('menu.' + children.language) }}</span>
                    </router-link>
                </li>
            </ul>
        </nav>
    </aside>
</template>

<script>
export default {
    name: "BackendMenuComponent",
    data: function () {
        return {
            activeParentId: 1,
            activeChildId: 0,
            sidebarOpen: false,
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        menus: function () {
            const menus = this.$store.getters.authMenu || [];

            // Enforce a stable top order regardless of persisted/cached menu payload
            const pinnedOrder = ["dashboard", "items", "dining_tables", "takeaway_types"];
            const pinnedIndex = (language) => {
                const idx = pinnedOrder.indexOf(language);
                return idx === -1 ? null : idx;
            };
            const fallbackIcons = {
                dining_tables: "lab lab-reserve-line",
                takeaway_types: "lab lab-bag-2",
                online_orders: "lab lab-total-orders",
                drivers: "lab lab-delivery-boy",
                online_users: "lab lab-customers",
            };

            const sortByPriority = (a, b) => {
                const ar = pinnedIndex(a?.language);
                const br = pinnedIndex(b?.language);
                if (ar !== null || br !== null) {
                    if (ar === null) return 1;
                    if (br === null) return -1;
                    if (ar !== br) return ar - br;
                }

                const ap = typeof a?.priority === "number" ? a.priority : parseInt(a?.priority ?? 0, 10) || 0;
                const bp = typeof b?.priority === "number" ? b.priority : parseInt(b?.priority ?? 0, 10) || 0;
                if (ap !== bp) return ap - bp;
                const ai = typeof a?.id === "number" ? a.id : parseInt(a?.id ?? 0, 10) || 0;
                const bi = typeof b?.id === "number" ? b.id : parseInt(b?.id ?? 0, 10) || 0;
                return ai - bi;
            };

            const normalizeLanguage = (m) => {
                // Some environments send a menu item with language = null which renders as "menu.null".
                // We normalize known URLs to a stable translation key.
                const url = m?.url ? String(m.url) : "";
                const lang = m?.language;
                if (lang !== null && lang !== undefined && String(lang).trim() !== "") return lang;

                if (url === "online-orders") return "online_orders";
                if (url === "table-orders") return "table_orders";
                if (url === "pos-orders") return "pos_orders";
                if (url === "pos") return "pos";

                return lang;
            };

            const reorderPosChildren = (children) => {
                if (!Array.isArray(children) || children.length === 0) return children;

                const list = [...children];
                const findIdx = (predicate) => list.findIndex(predicate);

                const tableIdx = findIdx((c) => c?.url === "table-orders" || c?.language === "table_orders");
                const onlineIdx = findIdx((c) => c?.url === "online-orders" || c?.language === "online_orders");

                // Move Online Orders just below Table Orders (only when both exist)
                if (tableIdx !== -1 && onlineIdx !== -1 && onlineIdx !== tableIdx + 1) {
                    const [online] = list.splice(onlineIdx, 1);
                    const insertAt = tableIdx + 1;
                    list.splice(insertAt, 0, online);
                }

                return list;
            };

            const isValidIcon = (icon) => {
                const s = icon === null || icon === undefined ? "" : String(icon).trim();
                if (!s) return false;
                // Some menu records have icon="lab" or "lab " which renders nothing.
                if (s === "lab" || s === "fa" || s === "fa-solid" || s === "fa-regular") return false;
                // Consider it valid only if it references a concrete icon class.
                return s.includes("lab-") || s.includes("fa-");
            };

            const normalizeMenu = (m) => {
                const language = normalizeLanguage(m);
                const icon = isValidIcon(m?.icon)
                    ? m.icon
                    : (fallbackIcons[language] ?? m?.icon);

                let children = m?.children;
                if (Array.isArray(children)) {
                    children = children
                        .map((c) => normalizeMenu(c))
                        .filter((c) => {
                            // Hide Online Orders from menu
                            const cUrl = String(c?.url ?? '').toLowerCase();
                            const cLang = String(c?.language ?? '').toLowerCase();
                            return cUrl !== 'online-orders' && cLang !== 'online_orders';
                        })
                        .sort(sortByPriority);
                    children = reorderPosChildren(children);
                }

                return { ...m, language, icon, children };
            };

            // Return a sorted copy so menu order always matches DB priority
            const normalized = [...menus]
                .filter((m) => {
                    // Hide Online Orders if it appears as a top-level menu
                    const mUrl = String(m?.url ?? '').toLowerCase();
                    const mLang = String(m?.language ?? '').toLowerCase();
                    return mUrl !== 'online-orders' && mLang !== 'online_orders';
                })
                .sort(sortByPriority)
                .map((m) => normalizeMenu(m));

            // If Online Orders is incorrectly sent as a top-level menu item (instead of under Pos & Orders),
            // relocate it under the "pos_and_orders" group and place it just below Table Orders.
            const posGroupIndex = normalized.findIndex((m) => m?.language === "pos_and_orders");
            if (posGroupIndex !== -1) {
                const posGroup = normalized[posGroupIndex];
                const posChildren = Array.isArray(posGroup?.children) ? [...posGroup.children] : [];

                const hasOnlineInChildren = posChildren.some(
                    (c) => c?.url === "online-orders" || c?.language === "online_orders"
                );

                const onlineTopIndex = normalized.findIndex(
                    (m, idx) =>
                        idx !== posGroupIndex &&
                        (m?.url === "online-orders" || m?.language === "online_orders")
                );

                if (!hasOnlineInChildren && onlineTopIndex !== -1) {
                    const [onlineTop] = normalized.splice(onlineTopIndex, 1);
                    posChildren.push(onlineTop);
                }

                posGroup.children = reorderPosChildren(posChildren);
                normalized[posGroupIndex] = normalizeMenu(posGroup);
            }

            return normalized;
        },
        sidebar() {
            return this.$store.getters['globalState/lists'].topSidebar;
        },
    },
    mounted() {
        this.defaultSidebarActive();

    },
    methods: {
        sidebarActive: function (e) {
            const activeMenu = document.querySelector('.db-sidebar-nav-item.active');
            if (activeMenu) {
                activeMenu.classList.remove('active');
            }
            e?.currentTarget?.classList?.add('active');
        },
        defaultSidebarActive: function () {
            if (document?.querySelector(".db-sidebar-nav-menu")?.classList?.contains("active")) {
                document?.querySelector('.db-sidebar-nav-menu')?.parentElement?.classList?.add('active');
            } else {
                document?.querySelector('.router-link-exact-active')?.parentElement?.classList?.add('active');
            }
        },
        handleSidebar: function () {
            this.sidebarOpen = !this.sidebar;
            this.$store.dispatch("globalState/set", { topSidebar: this.sidebarOpen });

            if (document?.querySelector(".db-sidebar")?.classList?.contains("active")) {
                document?.querySelector(".db-main")?.classList?.remove("expand");
                document?.querySelector(".db-sidebar")?.classList?.remove("active");
            } else {
                document?.querySelector(".db-sidebar")?.classList?.add("active");
                document?.querySelector(".db-main")?.classList?.add("expand");
            }
        },
        defaultSidebarActive: function () {
            const activeMenu = document.querySelector(".db-sidebar-nav-menu.active");
            if (activeMenu) {
                activeMenu.closest(".db-sidebar-nav-item")?.classList.add("active");
            }
        },
    },
    watch: {
        $route() {
            this.$nextTick(() => {
                document.querySelectorAll(".db-sidebar-nav-item").forEach(el => el.classList.remove("active"));
                this.defaultSidebarActive();
            });
        }
    },
}
</script>

