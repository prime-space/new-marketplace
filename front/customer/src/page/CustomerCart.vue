<template>
    <div>
        <div style="margin-bottom:20px;text-align:right">
            <span class="backBtn" @click="toAllOrders">{{$t('customer.cartItemList.allOrders')}}</span>
        </div>
        <BaseContainer>
            <cart v-if="cart && !listing.loading"
                  :cart="cart"
                  :items="items"
                  :currency="currency"
                  @goToGood="goToGood"
            />
            <div v-else>
                <!-- @TODO loading -->
                {{$t('customer.loading')}}
            </div>
        </BaseContainer>
    </div>
</template>

<script>
    import BaseContainer from "./../component/BaseContainer";
    import Cart from "./../component/Cart";

    export default {
        components: {
            Cart,
            BaseContainer,
        },
        data() {
            return {
                config: config,
                cart: null,
                cartForm: null,
                listing: null,
                items: [],
                currency: '=',
            };
        },
        created() {
            this.cartForm = Main.initForm(this, {
                method: 'get',
                url: '/crud/customerCart/' + this.$route.params.cartId,
                success: function (response) {
                    this.cart = response.body;
                }.bind(this),
            });
            this.listing = Main.initListingForm(this, {
                url: '/crud/customerCartItem',
                filters: {cartId: this.$route.params.cartId},
                sort: {id: 'asc'},
                success: function (response) {
                    let items = response.body.items;
                    this.currency = this.config.currencies[this.config.currency].sign;
                    for (let i in items) {
                        items[i].currency = this.currency;
                    }
                    this.items = items;
                }.bind(this),
            });
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
                this.cartForm.submit();
                this.listing.submit();
            },
            toAllOrders() {
                this.$router.push({name: 'customerOrderList'});
            },
            goToGood(cartItemId) {
                let cartId = this.$route.params.cartId;
                this.$router.push({name: 'customerCartItem', params: {cartId, cartItemId}});
            },
        },
    };
</script>

<style lang="scss" scoped>
</style>
