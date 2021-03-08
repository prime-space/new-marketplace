<template>
    <div>
        <transaction-list/>
    </div>
</template>

<script>
    import {page} from './../mixin/page';
    import TransactionList from "./../component/dashboard/TransactionList";

    export default {
        mixins: [page],
        components: {TransactionList},
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
