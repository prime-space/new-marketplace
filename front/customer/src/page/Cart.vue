<template>
    <div class="pageCart">
        <loader :isLoading="Main.loadingCounter>0"/>
        <BaseHeader @currencyChange="currencyChange" isShowCurrencySelect>
            <template v-slot:left>
                <i class="mdi mdi-cart mdi-36px"/>
                <span>{{ $t('cart.myCart') }}</span>
            </template>
            <template v-slot:right>
<!--                <i class="mdi mdi-shield-account mdi-36px"/>-->
<!--                <span>-->
<!--          <strong style="text-transform:uppercase;display:block;">{{ $t('cart.guarantee') }}</strong> {{ $t('cart.moneyBack') }}-->
<!--        </span>-->
            </template>
        </BaseHeader>

        <BaseContainer style="margin-top:35px;">
            <div v-if="items.length > 0">
                <CartProduct v-for="(item,i) in items"
                             :key="i"
                             :item="item"
                             :disabled="isDisabledElements"
                             :errors="[fixCartForm.getError('cartItems.'+i+'.productId'),fixCartForm.getError('cartItems.'+i+'.amount')]"
                             v-model="fixCartForm.data.cartItems[i]"
                             @increase="productIncrease"
                             @decrease="productDecrease"
                             @change="productChange"
                             @remove="productRemove"
                />
            </div>
            <div v-else class="cart__empty">
                Корзина пуста
            </div>
        </BaseContainer>

<!--        <div style="margin-top:16px">-->
<!--            <PromoCodeInput/>-->
<!--        </div>-->

        <form-error :error="fixCartForm.getError('cartItems')" style="margin-top:32px"/>
        <form-error :error="fixCartForm.getError('form')" style="margin-top:32px"/>

        <div class="pageCartOrderingZone">
            <div class="pageCartOrderingZone__email">
                <BaseContainer>
                    <div class="pageCartOrderingZone__title">{{ $t('cart.emailDelivery') }}</div>
                    <BaseInput v-model="fixCartForm.data.email"
                               :placeholder="$t('cart.email')"
                               :error="fixCartForm.getError('email')"
                               height="49px"
                    >
                        <template v-slot:icon>
                            <i class="mdi mdi-email mdi-inactive mdi-dark"/>
                        </template>
                    </BaseInput>
<!--                    <div class="pageCartOrderingZone__info">-->
<!--                        <BaseCheckbox-->
<!--                                :active="active"-->
<!--                                @click.native="active = !active"-->
<!--                                style="margin-top:16px;"-->
<!--                        />-->
<!--                        <p>{{ $t('cart.subscribeAgreement') }}</p>-->
<!--                    </div>-->
                </BaseContainer>
            </div>


            <div class="pageCartOrderingZone__payment">
                <BaseContainer>
                    <div class="title"><span>{{$t('cart.totalToPay')}}:</span> <span>{{totalView}}</span></div>
                    <BaseButton @click="fixCartFormSubmit"
                                :disabled="fixCartForm.loading"
                                :background="'#4CD09E'"
                                color="'#fff'"
                    >
                        {{$t('cart.continueToPay')}}
                    </BaseButton>
                    <!--                    <BaseButton-->
                    <!--                            style="margin-top:10px;"-->
                    <!--                            :background="'#F0F0F0'"-->
                    <!--                            color="'#CCCCCC'"-->
                    <!--                    >Продолжить оплату-->
                    <!--                    </BaseButton>-->
                </BaseContainer>
            </div>
        </div>
    </div>
</template>

<script>
    import BaseHeader from './../component/BaseHeader';
    import BaseContainer from './../component/BaseContainer';
    import BaseInput from './../component/BaseInput';
    import BaseButton from './../component/BaseButton';
    // import BaseCheckbox from './../component/BaseCheckbox';
    import CartProduct from './../component/CartProduct.vue';
    // import PromoCodeInput from './../component/PromoCodeField.vue';
    import Loader from './../component/Loader.vue';
    import FormError from './../component/FormError.vue';
    import PaymentForm from './../component/paymentForm';

    export default {
        components: {
            BaseHeader,
            BaseContainer,
            BaseInput,
            BaseButton,
            // BaseCheckbox,
            CartProduct,
            // PromoCodeInput,
            Loader,
            FormError
        },

        data() {
            return {
                config,
                Main,
                active: false,
                listing: null,
                setProductAmountForm: null,
                items: [],
                totalView: null,
                fixCartForm: null,
            };
        },
        created() {
            this.calcTotal();
            this.listing = Main.initListingForm(this, {
                url: '/crud/cartItem',
                sort: {id: 'asc'},
                success: function (response) {
                    let items = response.body.items;
                    let currencyView = this.config.currencies[this.config.currency].sign;
                    for (let i in items) {
                        items[i].currency = currencyView;
                    }
                    this.items = items;
                    this.calcTotal();
                }.bind(this),
            });
            this.setProductAmountForm = Main.initForm(this, {
                url: '/product/set-amount',
                isFormHandleValidationErrors: false,
            });
            this.fixCartForm = Main.initForm(this, {
                url: '/fix',
                data: {cartItems: []},
                success: function (response) {
                    let form = PaymentForm.initForm(response.body);
                    form.submit();
                }.bind(this),
            });
        },
        mounted() {
            this.init();
        },
        computed: {
            isProductActionsDisabled(productId) {
                return true === this.productActionDisables[productId];
            },
            isDisabledElements() {
                return this.setProductAmountForm.loading || this.fixCartForm.loading;
            },
        },
        methods: {
            init() {
                this.loadItems();
            },
            loadItems() {
                this.listing.submit();
            },
            productIncrease(productId) {
                let item = this.items.find(x => x.productId === productId);
                let amount = item.amount + 1;
                this.setProductAmount(item, amount, function () {
                    item.amount = amount;
                }.bind(this));
            },
            productDecrease(productId) {
                let item = this.items.find(x => x.productId === productId);
                let amount = item.amount - 1;
                this.setProductAmount(item, amount, function () {
                    if (amount > 0) {
                        item.amount = amount;
                    }
                }.bind(this));
            },
            productChange(e, productId) {
                let amount = e.target._value - 0;
                let item = this.items.find(x => x.productId === productId);
                this.setProductAmount(item, amount, function () {
                    item.amount = amount;
                }.bind(this));
            },
            productRemove(productId) {
                let item = this.items.find(x => x.productId === productId);
                let amount = 0;
                this.setProductAmount(item, amount, function () {
                }.bind(this));
            },
            setProductAmount(item, amount, success) {
                this.setProductAmountForm.submit({
                    data: {productId: item.productId, amount},
                    success: function () {
                        if (0 === amount) {
                            this.removeProductFromList(item);
                        }
                        success();
                        this.calcTotal();
                    }.bind(this),
                    error: function (response) {
                        //@TODO snack error
                        if (response.status === 410) {
                            this.$snack['danger']({text: response.body[0]});
                            this.removeProductFromList(item);
                        }
                    }.bind(this),
                });
            },
            removeProductFromList(item) {
                this.items = this.items.filter(function (x, i) {
                    if (x.productId === item.productId) {
                        // global.console.log(this.fixCartForm.errors.cartItems);
                        if (undefined !== this.fixCartForm.data.cartItems) {
                            this.fixCartForm.data.cartItems =
                                this.fixCartForm.data.cartItems.filter((x1, i1) => i1 !== i);
                        }
                        if (undefined !== this.fixCartForm.errors.cartItems) {
                            this.fixCartForm.errors.cartItems =
                                this.fixCartForm.errors.cartItems.filter((x1, i1) => i1 !== i);
                        }
                        return false;
                    }
                    return true;
                }.bind(this));
            },
            currencyChange() {
                this.loadItems();
            },
            calcTotal() {
                let currencyView = this.config.currencies[this.config.currency].sign;
                let total = 0;
                for (let i in this.items) {
                    let price = this.items[i].price - 0;
                    let amount = this.items[i].amount - 0;
                    total += price * amount;
                }
                this.totalView = total.toFixed(2) + currencyView;//@TODO currency scale
            },
            fixCartFormSubmit() {
                this.fixCartForm.submit();
            },
        }
    };
</script>
<style lang="scss" scoped>
    .ps {
        height: 250px;

    }

    .ps__rail-y {
        right: -100px;
    }

    i {
        font-size: 20px;
        padding-right: 20px;
    }

    .pageCartOrderingZone {
        margin: 30px 0 0;
        display: flex;
        justify-content: space-between;

        @media screen and (max-width: 800px) {
            flex-direction: column;
        }

        &__email {
            flex: 3;
        }

        &__payment {
            margin-left: 20px;
            flex: 2;

            @media screen and (max-width: 800px) {
                margin-top: 20px;
                margin-left: 0;
            }
        }

        &__info {
            font-size: 11px;
            color: #a8a8a8;

            display: flex;
            align-items: center;

            p {
                flex: 2;
                padding-left: 10px;
                margin: 16px 0 0;
            }
        }

        &__title {
            font-size: 16px;
            font-weight: 600;
            border-bottom: 3px solid #fafafa;
            padding-bottom: 5px;

            @media screen and (max-width: 800px) {
                padding-top: 15px;
                text-align: center;
            }
            display: flex;
            justify-content: space-between;
        }
    }

    .cart__empty {
        text-align: center;
        color: #a8a8a8;
    }
</style>
