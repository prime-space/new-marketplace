<template>
    <div>
        <div style="text-align:right">
            <span class="backBtn" @click="back">{{$t('customer.product.back')}}</span>
        </div>
        <detailed-product v-if="item" :item="item" :request-headers="requestHeaders"/>
        <div v-else><br/>{{$t('customer.loading')}}</div>
    </div>
</template>

<script>
    import DetailedProduct from "../component/DetailedProduct";
    export default {
        components: {DetailedProduct},
        data() {
            return {
                item: null,
                itemForm: null,
                requestHeaders: {'cart-token': this.$route.params.tokenKey},
            }
        },
        created() {
            this.itemForm = Main.initForm(this, {
                method: 'get',
                url: '/crud/cartItemOrderOnly/' + this.$route.params.cartItemId,
                headers: this.requestHeaders,
                success: function (response) {
                    this.item = response.body;
                }.bind(this),
            });
        },
        mounted() {
            this.init()
        },

        methods: {
            init() {
                this.itemForm.submit();
            },
            back() {
                let cartId = this.$route.params.cartId;
                let tokenKey = this.$route.params.tokenKey;
                this.$router.push({name: 'orderOnlyList', params: {cartId, tokenKey}});
            },
        }
    };
</script>

<style lang="scss" scoped>

</style>
