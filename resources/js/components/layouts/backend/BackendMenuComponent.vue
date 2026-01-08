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
            const pinnedOrder = ["dashboard", "items", "dining_tables", "takeaway"];
            const pinnedIndex = (language) => {
                const idx = pinnedOrder.indexOf(language);
                return idx === -1 ? null : idx;
            };
            const fallbackIcons = {
                dining_tables: "lab lab-reserve-line",
                takeaway: "lab lab-bag-line",
                takeaway_types: "lab lab-bag-line",
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

            // Return a sorted copy so menu order always matches DB priority
            return [...menus]
                .sort(sortByPriority)
                .map((m) => ({
                    ...m,
                    icon: (m?.icon && String(m.icon).trim()) ? m.icon : (fallbackIcons[m?.language] ?? m?.icon),
                    children: Array.isArray(m.children) ? [...m.children].sort(sortByPriority) : m.children,
                }));
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

