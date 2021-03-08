<template>
    <div class="productPage appContainer">
        <skeleton v-if="skeleton>0"/>
        <template v-else>
            <breadcrumbs/>

            <div class="productPage__header"
                 :style="[{background: product.background === null ? '#979797' : 'url(\''+config.cdn+'/'+product.background+'\')'}]"
            >
                <div class="productPage__header__content">
                    <div class="productPage__header__content__image">
                        <img :src="config.cdn+'/'+product.image" alt/>
                    </div>
                    <div class="productPage__header__content__info">
                        <ul>
                            <li>
                        <span class="bold">
                          <i class="mdi mdi-account-tie"></i> Продавец:
                        </span>
                                <span>{{product.sellerName}}</span>
                            </li>
                            <li>
                        <span class="bold">
                          <i class="mdi mdi-cart-arrow-down"></i> Продаж:
                        </span>
                                <span>{{product.salesNum}}</span>
                            </li>
                            <li v-if="product.reviewsPercent !== null"
                                class="productPage__header__content__info__reviewsSummary"
                            >
                                <span class="bold">
                                    <i class="mdi mdi-comment"></i> Отзывы о товаре:
                                </span>
                                <span :class="['productPage__header__content__info__reviewsSummary__grade', {'productPage__header__content__info__reviewsSummary__grade--bad': product.reviewsPercent < 50}]"
                                >{{grade}} ({{product.reviewsPercent}}%)</span>
                            </li>
                            <li v-else style="height:25px"></li>
                        </ul>

                        <h5 class="heading">{{product.name}}</h5>
                    </div>
                </div>
            </div>
            <div class="productPage__body">
                <div class="productPage__body__main">
                    <div class="productPage__body__main__description boxContainer">{{product.description}}</div>
                    <reviews class="productPage__body__main__reviews" :productId="product.id"/>
                </div>

                <div class="productPage__body__buy boxContainer">
                    <h6 class="productPage__body__buy__price">
                        {{product.priceView}} {{config.currencyShortView}}
                    </h6>
                    <div class="productPage__body__buy__availability">
                        Наличие
                        <span v-if="product.isInStock" class="productPage__body__buy__availability--yes">есть</span>
                        <span v-else class="productPage__body__buy__availability--no">нет</span>
                    </div>

                    <div class="productPage__body__buy__actions">
                        <a :href="product.url" class="productPage__body__buy__actions__now">Купить сейчас</a>
                        <v-btn @click="addToCart"
                               :loading="addToCartForm.loading"
                               class="productPage__body__buy__actions__toCart"
                               :elevation="0"
                               color="#fee11b"
                        >
                            <v-icon medium>mdi-basket</v-icon>
                        </v-btn>
                    </div>

                    <div class="productPage__body__buy__advantages">
                        <div>
                            <v-icon small color="#2b7bfe">mdi-speedometer</v-icon>
                            <span>Быстрое получение после оплаты</span>
                        </div>
                        <div>
                            <v-icon small color="#35d48a">mdi-shield-check</v-icon>
                            <span>Гарантия возврата средств</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import Breadcrumbs from '@/components/Breadcrumbs';
    import Skeleton from '@/components/product/Skeleton';
    import Reviews from '@/components/product/Reviews';
    import {page} from './../mixin/page';

    export default {
        mixins: [page],
        components: {Breadcrumbs, Skeleton, Reviews,},
        data() {
            return {
                config: config,
                skeleton: 2,
                product: null,
                addToCartForm: null,
            };
        },
        created() {
            Main.initForm(this, {
                url: '/crud/siteProduct/' + this.$route.params.productId,
                method: 'get',
                success: function (response) {
                    this.product = response.body;
                    this.$store.commit('breadcrumbs/fill', {productName: this.product.name});
                    let img = new Image();
                    img.src = config.cdn + '/' + this.product.image;
                    img.onload = this.onProductImageLoad;
                    if (this.product.background !== null) {
                        let background = new Image();
                        background.src = config.cdn + '/' + this.product.background;
                        background.onload = this.onProductImageLoad;
                    } else {
                        this.skeleton--;
                    }
                }.bind(this),
            }).submit();
            this.addToCartForm = Main.initForm(this, {
                url: '/add-to-cart/' + this.$route.params.productId,
                isFormHandleValidationErrors: false,
                snackSuccessMessage: 'Товар добавлен',
                success: function () {
                    config.cart.itemsAmount++;
                }.bind(this),
            });
        },
        computed: {
            grade() {
                let grade = 'чудовищные';
                if (this.product.reviewsPercent === 100) {
                    grade = 'превосходные';
                } else if (this.product.reviewsPercent > 89) {
                    grade = 'преимущественно положительные';
                } else if (this.product.reviewsPercent > 64) {
                    grade = 'в основном положительные';
                } else if (this.product.reviewsPercent > 49) {
                    grade = 'средние';
                } else if (this.product.reviewsPercent > 34) {
                    grade = 'в основном отрицательные';
                } else if (this.product.reviewsPercent > 24) {
                    grade = 'плохие';
                }

                return grade;
            },
        },
        methods: {
            onProductImageLoad() {
                this.skeleton--;
            },
            addToCart() {
                this.addToCartForm.submit();
            },
        },
    };
</script>

<style lang="scss">
    .productPage {
        margin-bottom: 15px;

        &__body {
            display: flex;
            flex-direction: row;
            align-items: flex-start;

            @media screen and (max-width: 991px) {
                flex-direction: column-reverse;
            }

            &__main {
                margin-right: 15px;
                width: 66.666667%;

                @media screen and (max-width: 991px) {
                    width: 100%;
                    margin-right: 0;
                }

                &__description {
                    font-size: 14px;
                    font-weight: normal;
                    line-height: 20px;
                    text-align: left;
                    color: #1c1c1c;
                    white-space: pre-wrap;
                }

                &__reviews {
                    margin-top: 20px;
                }
            }

            &__buy {
                margin-left: 15px;
                width: 33.333333%;

                @media screen and (max-width: 991px) {
                    width: 100%;
                    margin-left: 0;
                    margin-bottom: 15px;
                }


                &__price {
                    font-size: 24px;
                    font-weight: 500;
                }

                /*.sale {*/
                /*    text-decoration: line-through;*/
                /*    font-size: 16px;*/
                /*}*/

                &__actions {
                    margin: 25px 0 0;
                    display: flex;
                    justify-content: space-between;

                    &__now {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: #2774f0;
                        padding: 16px 0;
                        flex: 2;
                        margin-right: 10px;
                        font-size: 16px;
                        color: #fff !important;
                        border-radius: 8px;
                    }

                    &__toCart {
                        border-radius: 8px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 13px 25px;
                        text-decoration: none;
                        color: #1c1c1c !important;
                        text-align: center;
                        font-size: 24px;
                        transition: 0.3s;
                        height: 56px !important;

                        &:hover {
                            background: #43bf88 !important;
                            color: #fff !important;
                        }
                    }

                    .buy-now,
                    .add-to-cart {
                        border-radius: 8px;
                    }
                }

                &__availability {
                    font-size: 13px;

                    &--yes {
                        color: #35d48a;
                    }

                    &--no {
                        color: #ff1b1b;
                    }
                }

                &__advantages {
                    margin: 25px 0 0;
                    font-size: 13px;

                    /*.advantage {*/
                    /*    font-weight: normal;*/
                    /*    font-size: 13px;*/
                    /*    text-align: left;*/
                    /*    color: #828282;*/

                    span {
                        padding-left: 10px;
                    }

                    i {
                        margin-bottom: 2px
                    }

                    /*}*/

                    .blue {
                        color: #2b7bfe;
                    }

                    .green {
                        color: #35d48a;
                    }
                }


            }
        }

        &__header {
            margin: 16px 0 85px 0;
            /*background: url('../assets/img/product-header.png');*/
            background-size: cover;
            height: 170px;
            border-radius: 8px;
            position: relative;

            @media screen and (max-width: 991px) {
                background: none !important;
                height: auto;
                margin-bottom: 20px;
            }

            &__content {
                display: flex;
                flex-wrap: wrap;
                position: absolute;
                top: 50px;
                width: 100%;

                @media screen and (max-width: 991px) {
                    position: relative;
                    top: 0;
                    flex-direction: column;
                }

                /*@media screen and (min-width: 1200px) {*/
                /*    position: relative;*/
                /*}*/

                &__image {
                    margin-left: 8.333333%;
                    flex: 0 0 33.333333%;
                    @media screen and (max-width: 1100px) {
                        text-align: center;
                    }

                    img {
                        border-radius: 8px;
                    }

                    /*@media screen and (max-width: 500px) {*/
                    /*    img {*/
                    /*        width: 100%;*/
                    /*    }*/
                    /*}*/
                }

                &__info {
                    flex: 0 0 58.333333%;
                    max-width: 58.333333%;
                    padding-left: 30px;
                    /*@media screen and (min-width: 1500px) {*/
                    /*<!--    margin-left: -50px;-->*/
                    /*}*/

                    &__reviewsSummary {
                        @media screen and (max-width: 991px) {
                            display: none;
                        }

                        &__grade {
                            color: #35d48a;
                            &--bad{
                                color: #ff1b1b;
                            }
                        }
                    }

                    ul {
                        margin: 13px 0 0;
                        padding: 0;
                        list-style: none;

                        @media screen and (max-width: 991px) {
                            padding-left: 0 !important;
                        }

                        li {
                            &:not(:first-of-type) {
                                margin-top: 10px;
                            }

                            color: rgba(#ffffff, 0.75);

                            @media screen and (max-width: 991px) {
                                color: #1c1c1c;
                            }

                            .bold {
                                i {
                                    padding-right: .5rem !important
                                }

                                font-weight: 500;
                                color: #fff;

                                @media screen and (max-width: 991px) {
                                    color: #1c1c1c;
                                }
                            }
                        }
                    }

                    .heading {
                        padding-top: 20px;
                        font-size: 18px;
                        font-weight: 500;
                    }
                }
            }
        }
    }
</style>
