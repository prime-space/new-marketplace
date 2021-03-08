<template>
    <div class="transactionDescription">
        <div v-if="1 === id">
                Продажа
            <router-link :to="{name: 'order', params: {id: data.cartItemId}}" target="_blank">
                #{{data.cartItemId}}
            </router-link>
            товара
            <router-link :to="{name: 'productEdit', params: {id: data.productId}}" target="_blank">
                {{data.productName}}
            </router-link>
            в количестве <b>{{data.quantity}}</b> шт.
        </div>
        <div v-else-if="2 === id">
            Партнерские начисления за продажу товара
            <span class="bold">{{data.productName}}</span>
            продавца
            <span class="bold">{{data.sellerName}}</span>
            в количестве <b>{{data.quantity}}</b> шт.
        </div>
        <div v-else-if="3 === id">
            Выплата <span class="bold">#{{data.payoutId}}</span> на
            <span class="bold">{{data.payoutMethodName}}</span>
            &nbsp;<span class="bold">{{data.receiver}}</span>
        </div>
        <div v-else-if="4 === id">
            Возврат средств по выплате <span class="bold">#{{data.payoutId}}</span>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            id: Number,
            data: Object,
        },
    }
</script>

<style>
</style>
