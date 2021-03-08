<template>
    <div>
        <div style="text-align:right">
            <span class="backBtn" @click="back">{{$t('customer.product.back')}}</span>
        </div>
        <detailed-product v-if="item" :item="item"/>
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
            }
        },
        created() {
            this.itemForm = Main.initForm(this, {
                method: 'get',
                url: '/crud/customerCartItem/' + this.$route.params.cartItemId,
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
                this.$router.push({name: 'customerCartItemList', params: {cartId}});
            },
        }
    };
</script>

<style lang="scss" scoped>
</style>
