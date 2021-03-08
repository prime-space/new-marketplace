<template>
    <div class="cart-product">
        <div class="product">
            <img class="product__image" :src="item.image" alt/>
            <div class="info-container">
                <div class="product__name">{{item.name}}</div>
                <div class="product__details">
                    <p>
                        <i class="mdi mdi-account-check"/>
                        <span class="muted">{{ $t('cart.product.seller') }}: </span>
                        <span>{{ item.seller }}</span>
                    </p>
                    <p class="padding">
                        <i class="mdi mdi-basket shopping-cart"/>
                        <span class="muted">{{ $t('cart.product.sales') }}: </span>
                        <span>{{ item.soldsCount }}</span>
                    </p>
                </div>
            </div>
            <div class="product__price">
                <div style="padding:0 5px 5px 0;">
                    <!--          <span class="sale">{{sale}}%</span>-->
                    <!--          <span class="divider">/</span>-->
                    {{item.price}}{{item.currency}}
                </div>
                <div class="count">
                    <span>{{ $t('cart.product.amount') }}:</span>
                    <button class="button-count" @click="$emit('decrease', item.productId)" :disabled="disabled">
                        <span>-</span>
                    </button>
                    <input type="text" v-model="item.amount" @change="$emit('change', $event, item.productId)"/>
                    <button @click="$emit('increase', item.productId)" :disabled="disabled" class="button-count">
                        <span>+</span>
                    </button>
                </div>
            </div>
        </div>
<!--        <div>-->
            <form-error v-for="(error,i) in errors" :key="i" :error="error" small/>
            <button class="remove" @click="$emit('remove', item.productId)" :disabled="disabled">
                <i class="trash mdi mdi-trash-can-outline"/> {{ $t('cart.product.remove') }}
            </button>
<!--        </div>-->
    </div>
</template>

<script>
    import FormError from './FormError.vue';

    export default {
        components: {
            FormError,
        },
        props: {
            item: {
                type: Object,
                required: true,
            },
            disabled: {
                type: Boolean,
                default: false,
            },
            value: {
                type: Object
            },
            errors: {
                type: Array,
            }
        },
        created() {
            this.sync();
        },
        watch: {
            item: {
                handler: function () {
                    this.sync();
                },
                deep: true,
            }
        },
        methods: {
            sync() {
                this.$emit('input', {productId: this.item.productId, amount: this.item.amount});
            }
        },
    };
</script>

<style lang="scss" scoped>
    .cart-product {
        display: flex;
        flex-direction: column;
        border-bottom: 3px solid #fafafa;
    }

    .product {
        margin: 10px 0;
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fafafa;

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
                margin: 0px;
                display: flex;
                align-items: center;
                white-space: pre-wrap;

                i {
                    height: 20px;
                    width: 20px;
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
            text-align: right;

            .sale {
                color: #ffb367;
            }

            .divider {
                color: #f0f0f0;
                font-size: 16px;
            }

            .count {
                font-size: 11px;
                color: #a8a8a8;

                span {
                    padding-right: 10px;
                }

                .button-count {
                    display: inline-flex;
                    position: relative;
                    margin: 0 10px;
                    height: 15px;
                    width: 15px;
                    justify-content: center;
                    align-items: center;

                    border-radius: 50%;
                    color: #fff;
                    font-weight: bold;
                    border: none;
                    cursor: pointer;
                    background: #cccccc;
                    outline: none;
                    padding: 0;
                    font-size: 9px;
                    text-align: center;

                    vertical-align: middle;

                    span {
                        display: block;
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        padding-right: 0;
                    }
                }

                input {
                    width: 24px;
                    height: 18px;
                    background: #f0f0f0;
                    text-align: center;
                    color: #1c1c1c;
                    border: none;
                    outline: none;
                }
            }
        }
    }

    .remove {
        display: flex;
        align-items: center;
        color: #a8a8a8;
        background: transparent;
        border: none;
        outline: none;
        cursor: pointer;
        align-self: flex-end;
        font-size: 14px;
        margin: 11px 0 20px;

        img {
            padding-right: 10px;
        }

        .trash {
            color: #ff6060;
            padding-right: 5px;
        }
    }

    .grey {
        background: #fafafa;
    }

    i {
        color: #0177fd;
        font-size: 16px;
    }
</style>
