<template>
    <div class="cartItem" :class="{grey:grey}">
        <div class="cartItem__info-container">
            <div class="cartItem__name">{{$t('customer.cartItem.order')}} №{{item.id}}</div>
            <div class="cartItem__details">
                <p class="padding">
                    <i class="mdi mdi-calendar-clock"/>
                    <span class="muted">&nbsp;{{$t('customer.cartItem.date')}}</span>
                    <span>&nbsp;{{ item.createdDate }}</span>
                </p>
            </div>
        </div>
        <div class="cartItem__price">{{item.totalProductsAmountView}}{{item.currency}}</div>
    </div>
</template>

<script>
    export default {
        props: {
            item: {
                type: Object,
                required: true,
            },
            grey: {
                type: Boolean,
                default: false
            },
        }
    };
</script>

<style lang="scss" scoped>
    .cartItem {
        cursor: pointer;
        padding: 10px;
        display: flex;
        justify-content: space-between;
        margin: 20px 0;

        @media screen and (max-width: 900px) {
            flex-direction: column;
            align-items: center;
        }

        .cartItem__info-container {
            padding-top: 5px;
            flex: 2;
            margin-left: 20px;

            @media screen and (max-width: 900px) {
                flex-direction: column;
                align-items: center;
                text-align: center;
                margin-left: 0;
            }

            @media screen and (max-width: 700px) {
                margin-top: 20px;
            }
        }

        &__name {
            font-size: 15px;
            font-weight: 500;
        }

        &__details {
            margin-top: 8px;
            font-size: 11px;
            display: flex;
            align-items: center;

            @media screen and (max-width: 900px) {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            p {
                margin: 0;
                display: flex;
                align-items: center;
                white-space: pre-wrap;

                i {
                    font-size: 14px;
                    color: #0177FD;
                }
            }

            .padding {
                padding-left: 15px;
            }

            .muted {
                color: #a8a8a8;
            }
        }

        &__price {
            padding-top: 5px;
            font-size: 18px;
            font-weight: 500;
            color: #4cd09e;
        }
    }

    .grey {
        background: #fafafa;
    }
</style>
