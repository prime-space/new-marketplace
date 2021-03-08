<template>
    <div class="partnershipSellerAgentsPage">
        <page-tab :tabs="[{hash: 'mine', name: 'Мои агенты'},{hash: 'search', name: 'Поиск агентов'},]">
            <v-tab-item value="mine">
                <agents-tab/>
            </v-tab-item>
            <v-tab-item value="search">
                <search-tab/>
            </v-tab-item>
        </page-tab>
    </div>
</template>

<script>
    import {page} from './../../../mixin/page';
    import PageTab from './../../../component/PageTab';
    import SearchTab from './../../../component/partnership/seller/agents/SearchTab';
    import AgentsTab from './../../../component/partnership/seller/agents/AgentsTab';

    export default {
        mixins: [page],
        components: {PageTab, SearchTab, AgentsTab},
        data() {
            return {}
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
            },
        }
    }
</script>

<style>
</style>
