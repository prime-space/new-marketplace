<template>
    <div class="productCard">
        <template v-if="skeleton">
            <v-sheet :color="`grey lighten-4`">
                <v-skeleton-loader class="mx-auto" type="image, article, table-heading"/>
            </v-sheet>
        </template>
        <template v-else>
            <router-link :to="{name: 'product', params: {productId: product.id}}">
                <img :src="config.cdn+'/'+product.image" class="productCard__img" :alt="product.name"/>
            </router-link>
            <div class="productCard__body">
                <div class="productCard__body__name">
                    <router-link :to="{name: 'product', params: {productId: product.id}}">
                        {{product.name}}
                    </router-link>
                </div>
                <div class="productCard__body__info">
                    <span class="productCard__body__info__price">{{product.priceView}} <sup>{{config.currencyShortView}}</sup></span>
                    <!--                    <span class="productCard__body__info__reviews"><i class="mdi mdi-emoticon-happy"></i> 323</span>-->
                </div>

                <div class="productCard__body__sells">
                    <span><i class="mdi mdi-account-tie"></i> {{product.sellerName}}</span>
                    <span><i class="mdi mdi-cart-arrow-down"></i> {{product.salesNum}}</span>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    export default {
        props: {
            skeleton: {
                type: Boolean,
                default: false
            },
            product: {
                type: Object,
            },
        },
        data() {
            return {
                config: config,
            };
        },
    }
</script>

<style lang="scss">
    .productCard {
        background: #fff;
        border-radius: 8px;
        border: none;
        width: 255px;
        margin-bottom: 15px;

        .v-skeleton-loader__image {
            height: 147px !important;
        }

        &__img {
            height: 136px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        @media (min-width: 992px) and (max-width: 1199px) {
            width: 450px;
            .v-skeleton-loader__image {
                height: 255px !important;
            }
            &__img {
                height: 240px;
            }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            width: 330px;
            .v-skeleton-loader__image {
                height: 187px !important;
            }
            &__img {
                height: 176px;
            }
        }

        @media screen and (max-width: 767px) {
            width: 510px;
            .v-skeleton-loader__image {
                height: 290px !important;
            }
            &__img {
                height: 272px;
            }
        }

        @media screen and (max-width: 600px) {
            width: 100%;
            min-width: 280px;
            /*.v-skeleton-loader__image {*/
            /*    height: 290px !important;*/
            /*}*/
            &__img {
                height: auto;
            }
        }

        &__img {
            width: 255px;
            @media (min-width: 992px) and (max-width: 1199px) {
                width: 450px;
            }
            @media (min-width: 768px) and (max-width: 991px) {
                width: 330px;
            }
            @media screen and (max-width: 767px) {
                width: 510px;
            }
            @media screen and (max-width: 600px) {
                width: 100%;
            }
        }

        &__body {
            padding: 20px 25px;
            color: #1c1c1c;
            font-size: 13px;

            &__info {
                padding: 15px 0 0;
                display: flex;
                justify-content: flex-start;

                &__price {
                    font-weight: 500;
                    font-size: 16px;
                }

                &__reviews {
                    padding-left: 20px;
                    color: #35d48a;
                    font-weight: 500;

                    i {
                        font-size: 16px;
                    }
                }
            }

            &__sells {
                margin: 30px 0 0;
                display: flex;
                justify-content: space-between;

                span {
                    color: rgba(#1c1c1c, 0.35);

                    i {
                        color: #2b7bfe;
                        font-size: 16px;
                        padding-right: 10px;
                    }
                }
            }

            &__name {
                a {
                    color: #1c1c1c !important;
                }
            }
        }
    }
</style>
