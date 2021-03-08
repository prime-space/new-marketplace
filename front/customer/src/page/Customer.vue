<template>
    <div class="customer">
        <loader :isLoading="Main.loadingCounter>0"/>
        <BaseHeader style="margin-bottom:22px">
            <template v-slot:left>
                <i class="mdi mdi-home mdi-36px"/> {{$t('customer.inside.header')}}
            </template>
            <template v-slot:right>
                <v-btn icon dark href="/logout"><i class="mdi mdi-exit-to-app mdi-24px"/></v-btn>
            </template>
        </BaseHeader>
        <router-view/>
    </div>
</template>

<script>
    import BaseHeader from "./../component/BaseHeader";
    import Loader from './../component/Loader.vue';

    export default {
        components: {
            BaseHeader,
            Loader,
        },
        data: () => ({
            config: config,
            Main,
        }),
        mounted() {
            this.init()
        },
        methods: {
            init() {
            },
        }
    };
</script>

<style lang="scss" scoped>
</style>
