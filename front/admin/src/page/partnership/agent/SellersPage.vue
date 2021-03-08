<template>
    <div class="partnershipAgentSellersPage">
        <page-tab :tabs="[{hash: 'mine', name: 'Мои продавцы'},{hash: 'offers', name: 'Предложения'},]">
            <v-tab-item value="mine">
                <sellers-tab/>
            </v-tab-item>
            <v-tab-item value="offers">
                <offers-tab/>
            </v-tab-item>
        </page-tab>
    </div>
</template>

<script>
    import {page} from './../../../mixin/page';
    import PageTab from './../../../component/PageTab';
    import SellersTab from "../../../component/partnership/agent/sellers/SellersTab";
    import OffersTab from "../../../component/partnership/agent/sellers/OffersTab";

    export default {
        mixins: [page],
        components: {PageTab, SellersTab, OffersTab},
        data () {
            return {
            }
        },
        mounted(){
            this.init()
        },
        methods: {
            init () {
            }
        }
    }
</script>

<style>
</style>
