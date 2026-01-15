<template>
    <section class="pt-8 pb-16">
        <div class="container max-w-3xl">
            <router-link :to="{ name: 'online.menu', params: { branchId: this.$route.params.branchId } }"
                class="text-xs font-medium inline-flex mb-3 items-center gap-2 text-primary">
                <i class="lab lab-undo lab-font-size-16"></i>
                <span>{{ $t('label.back_to_home') }}</span>
            </router-link>

            <div class="mb-6">
                <h2 class="text-[26px] leading-10 font-semibold capitalize mb-2">
                    {{ page.title }}
                </h2>
                <div v-if="page.image" class="w-full mb-6">
                    <img :src="page.image" alt="image">
                </div>
                <div class="ql-editor" v-html="page.description"></div>
            </div>
            <TemplateManagerComponent :templateId="page.template_id" />

        </div>
    </section>
</template>

<script>
import TemplateManagerComponent from "../table/components/TemplateManagerComponent";
import 'vue3-quill/lib/vue3-quill.css';

export default {
    name: "OnlinePageComponent",
    components: { TemplateManagerComponent },
    computed: {
        page: function () {
            return this.$store.getters['frontendPage/show'];
        }
    },
    mounted() {
        this.pageSetup();
    },
    methods: {
        pageSetup: function () {
            if (Object.keys(this.$route.params).length > 0 && typeof this.$route.params.pageSlug === 'string') {
                this.$store.dispatch('frontendPage/show', this.$route.params.pageSlug).then(res => { }).catch((err) => { })
            }
        }
    },
    watch: {
        $route() {
            this.pageSetup();
        }
    }
}
</script>

