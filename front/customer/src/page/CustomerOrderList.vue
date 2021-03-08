<template>
    <div class="customerOrderList">
        <div class="title">{{$t('customer.orderList.myOrders')}}</div>
        <BaseContainer style="margin-top:15px">
            <div v-if="listing.loading">
                <!--   @TODO loading     -->
                {{$t('customer.loading')}}
            </div>
            <div v-else>
                <cart-item v-for="(item,i) in items"
                           :key="i"
                           :item="item"
                           @click.native="goToCart(item.id)"
                />
            </div>
        </BaseContainer>
    </div>
</template>

<script>
    import BaseContainer from "./../component/BaseContainer";
    import CartItem from "../component/CartItem";

    export default {
        components: {
            CartItem,
            BaseContainer,
        },
        data: () => ({
            config: config,
            items: [],
            listing: null,
        }),
        created() {
            this.listing = Main.initListingForm(this, {
                url: '/crud/customerCart',
                sort: {id: 'desc'},
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
                this.listing.submit();
            },
            goToCart(cartId) {
                this.$router.push({name: 'customerCartItemList', params: {cartId}});
            },
        }
    };
</script>

<style lang="scss">
</style>
