<template>
    <div>
        <div style="margin-bottom:20px;text-align:right">
            <span class="backBtn" @click="gotoAllOrders">{{$t('customer.orderList.allOrders')}}</span>
        </div>
        <BaseContainer>
            <cart v-if="items.length > 0"
                  :cart="$attrs.cartData"
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
                items: [],
                currency: '=',
            };
        },
        created() {
            this.listing = Main.initListingForm(this, {
                url: '/crud/cartItemOrderOnly',
                headers: {'cart-token': this.$route.params.tokenKey},
                sort: {id: 'asc'},
                success: function (response) {
                    let items = response.body.items;
                    this.currency = this.config.currencies[this.config.currency].sign;
                    this.items = items;
                    for (let i in items) {
                        items[i].currency = this.currency;
                    }
                }.bind(this),
            });
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
                this.listing.submit();
            },
            gotoAllOrders() {
                location.href = '/';
            },
            goToGood(cartItemId) {
                let cartId = this.$route.params.cartId;
                let tokenKey = this.$route.params.tokenKey;
                this.$router.push({name: 'orderOnlyCartItem', params: {cartId, tokenKey, cartItemId}});
            },
        },
    };
</script>

<style lang="scss" scoped>
</style>
