<template>
    <div class="cart">
        <div class="list-item">
            <i class="item-icon fas fa-info-circle"/>
            <span>{{$t('customer.orderList.orderNumber')}}: </span>
            <span class="cart__listItem__black">{{cart.id}}</span>
            <span> ({{$t('customer.orderList.created')}}: {{cart.createdDate}})</span>
            <span class="cart__listItem__black"> {{$t('customer.orderList.amount')}} {{cart.totalProductsAmountView}}{{currency}}</span>
        </div>
        <Notification v-if="someOutOfStock" error bordered>
            {{$t('customer.orderList.outOfStock')}}
            <br/>{{$t('customer.orderList.contectSupport')}}
        </Notification>
        <product v-for="(item,i) in items"
                 :key="i"
                 :item="item"
                 @click.native="$emit('goToGood', item.id)"
        />
    </div>
</template>

<script>
    import Product from "./../component/Product";
    import Notification from "./../component/Notification";

    export default {
        components: {
            Product,
            Notification,
        },
        props: {
            cart: Object,
            items: Array,
            currency: String,
        },
        data() {
            return {
                someOutOfStock: false,
            };
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
                for (let i in this.items) {
                    this.items[i].currency = this.currency;
                    if (this.items[i].amountInFact < this.items[i].amount) {
                        this.someOutOfStock = true;
                    }
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    .list-item {
        margin: 5px 0;
        padding: 10px 15px;
        background: #FAFAFA;
        border-radius: 8px;

        color: #A8A8A8;
        font-weight: 500;

        .cart__listItem__black {
            color: #1C1C1C;
            font-weight: 400;
        }

        .item-icon {
            color: #1C1C1C;
            font-size: 20px;
            padding-right: 20px;
        }
    }
</style>
