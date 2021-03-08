<template>
    <div class="product" :class="{grey:grey}">
      <img class="product__image" :src="item.image" alt/>
        <div class="info-container">
            <div class="product__name">{{item.name}}</div>
            <div class="product__details">
                <p>
                    <i class="mdi mdi-account-check"/>
                    <span class="muted"> {{$t('customer.product.seller')}}: &emsp; </span>
                    <span>{{ item.seller }}</span>
                </p>
            </div>
        </div>
        <div class="product__price">{{item.priceView}}{{item.currency}}</div>
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
    .product {
        cursor: pointer;
        padding: 10px;
        display: flex;
        justify-content: space-between;
        margin: 20px 0;

        @media screen and (max-width: 900px) {
            flex-direction: column;
            align-items: center;
        }

        &__image {
            width: 134px;
            height: 63px;
            border-radius: 10px;
        }

        .info-container {
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
            font-size: 16px;
            font-weight: 500;
        }

        &__details {
            margin-top: 8px;
            font-size: 13px;
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
