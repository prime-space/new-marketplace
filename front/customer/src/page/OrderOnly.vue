<template>
    <div class="orderOnly">
        <loader :isLoading="Main.loadingCounter>0"/>
        <BaseHeader style="margin-bottom:22px">
            <template v-slot:left>
                <i class="mdi mdi-home mdi-36px"/> {{$t('customer.order.getting')}}
            </template>
        </BaseHeader>

        <div v-if="isCartPaid">
            <router-view :cartData="cartData"/>
        </div>
        <BaseContainer v-else style="margin-top:60px">
            <div class="orderOnlyInfo">
                <div class="orderOnlyInfo__image">
                    <img src="../asset/images/logo.svg" alt="Crocus Pay"/>
                </div>

                <div v-if="isCartNotFound" class="orderOnlyInfo__text">
                    <p>{{$t('customer.order.notFound')}}</p>
                    <p>{{$t('customer.order.seekIn')}} <a href="/">{{$t('customer.order.inCustomerSpace')}}</a></p>
                </div>
                <div v-else-if="cartData" class="orderOnlyInfo__text">
                    <div v-if="2 === cartData.statusId">
                        <p class="bold">{{$t('customer.order.waiting')}}</p>
                        <div class="spinner-wrapper">
                            <div class="spinner spinner-content"></div>
                        </div>
                        <p style="margin-top:15px">{{$t('customer.order.ifNotPaid')}} <a href="#" @click="backToPayment">
                            {{$t('customer.order.paymentSystem')}}</a>.</p>
                    </div>
                </div>
                <div v-else class="orderOnlyInfo__text">
                    <div class="spinner-wrapper">
                        <div class="spinner spinner-content"></div>
                    </div>
                    <p>{{$t('customer.order.wait')}}</p>
                </div>
            </div>
        </BaseContainer>
    </div>
</template>

<script>
    import BaseHeader from "./../component/BaseHeader";
    import BaseContainer from "./../component/BaseContainer";
    import PaymentForm from "../component/paymentForm";
    import Loader from './../component/Loader.vue';

    export default {
        components: {
            BaseHeader,
            BaseContainer,
            Loader,
        },
        data() {
            return {
                cartData: null,
                cartForm: null,
                isCartNotFound: false,
                timer: null,
                items: [],
                Main,
            };
        },
        created() {
            this.cartForm = Main.initForm(this, {
                method: 'get',
                url: '/crud/cartOrderOnly/' + this.$route.params.cartId,
                headers: {'cart-token': this.$route.params.tokenKey},
                success: function (response) {
                    this.cartData = response.body;
                    if (2 === this.cartData.statusId) {
                        this.clearTimer();
                        this.timer = setTimeout(function () {
                            this.getCartState();
                        }.bind(this), 3000);
                    }
                }.bind(this),
                error: function (response) {
                    if (response.status === 404) {
                        this.isCartNotFound = true;
                    }
                }.bind(this),
            });
        },
        mounted() {
            this.init()
        },
        computed: {
            isCartPaid() {
                return false === this.isCartNotFound && null !== this.cartData && 3 === this.cartData.statusId;
            }
        },
        methods: {
            init() {
                this.getCartState();
            },
            getCartState() {
                this.clearTimer();
                this.cartForm.submit();
            },
            clearTimer() {
                if (null !== this.timer) {
                    clearTimeout(this.timer);
                }
            },
            backToPayment() {
                let form = PaymentForm.initForm(this.cartData.backToPaymentFormData);
                form.submit();
            },
        },
    };
</script>

<style lang="scss" scoped>
    i {
        padding-right: 10px;
        font-size: 20px;
    }

    .orderOnlyInfo {
        text-align: center;
        font-size: 15px;

        &__image {
            margin-top: 10px;
            border-bottom: 4px solid #fafafa;
            display: inline-block;
            padding-bottom: 20px;
        }

        &__text {
            margin-top: 26px;
        }
    }

    .authentication {
        margin: 55px 0 0;
        padding: 0 70px;

        @media screen and (max-width: 600px) {
            padding: 0 5px;
        }

        .captcha {
            margin-top: 15px;
            display: flex;

            @media screen and (max-width: 600px) {
                display: block;
                text-align: center;
            }

            * {
                flex: 2;
            }

            .captcha__image {
                flex: 1;

                @media screen and (max-width: 600px) {
                    margin: 15px auto 0;
                }
            }
        }

        .submit {
            margin-top: 15px;
        }
    }
</style>
