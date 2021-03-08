<template>
    <div class="accountPage">
        <page-tab :tabs="[{hash: 'info', name: 'Информация'},{hash: 'api', name: 'API'},]">
            <v-tab-item value="info">
                <info-tab/>
            </v-tab-item>
            <v-tab-item value="api">
                <api-tab/>
            </v-tab-item>
        </page-tab>
    </div>
</template>

<script>
    import {page} from './../mixin/page';
    import PageTab from './../component/PageTab';
    import ApiTab from './../component/account/ApiTab';
    import InfoTab from './../component/account/InfoTab';

    export default {
        mixins: [page],
        components: {PageTab, ApiTab, InfoTab},
    }
</script>

<style>
</style>
