<template>
    <div class="partnershipAgentProductsPage">
        <page-tab :tabs="tabs">
            <template v-slot:default="slotProps">
                <v-tab-item value="all">
                    <all-tab v-if="slotProps.tab==='all'"/>
                </v-tab-item>
                <v-tab-item value="groups">
                    <groups-tab v-if="slotProps.tab==='groups'"/>
                </v-tab-item>
            </template>
        </page-tab>
    </div>
</template>

<script>
    import {page} from './../../../mixin/page';
    import AllTab from "../../../component/partnership/agent/products/AllTab";
    import GroupsTab from "../../../component/partnership/agent/products/GroupsTab";
    import PageTab from "../../../component/PageTab";

    export default {
        mixins: [page],
        components: {PageTab, AllTab, GroupsTab},
        data() {
            return {
                tabs: [{hash: 'all', name: 'Все товары'}, {hash: 'groups', name: 'Мои группы'},],
            }
        },
    }
</script>
