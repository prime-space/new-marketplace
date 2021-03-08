<template>
    <div class="detailedProduct">
        <div class="detailedProductInfo">
            <div class="title">
                <span>{{$t('customer.product.yourGood')}}</span>
            </div>
            <Notification class="good" v-for="(productObject, i) in item.objects" :key="i" success bordered>
                <span>{{productObject}}</span>
            </Notification>
        </div>

        <div class="detailedProductInfoDetails">
            <div class="title">{{$t('customer.product.details')}}</div>
            <div style="margin-top:20px;">
                <BaseContainer>
                    <div class="detailedProductInfoDetails__listItem">
                        <i class="item-icon fas fa-box"/>
                        <span>{{$t('customer.product.productName')}}: </span>
                        <span class="detailedProductInfoDetails__listItem__black">{{item.name}}</span>
                        <!--                            <a href="#">{{item.name}}</a>-->
                    </div>

                    <div class="detailedProductInfoDetails__listItem">
                        <i class="item-icon fas fa-user-tie"/>
                        <span>{{$t('customer.product.aboutSeller')}}: </span>
                        <span class="detailedProductInfoDetails__listItem__black">{{item.seller}}</span>
                    </div>
                </BaseContainer>
            </div>
        </div>
        <div class="detailedProductTabs">
            <div :class="['detailedProductTabs__item', {detailedProductTabs__item__active: tab===0}]"
                 @click="tab=0"
            >
                {{$t('customer.sellerMessaging.title')}}
            </div>
            <div :class="['detailedProductTabs__item', {detailedProductTabs__item__active: tab===1}]"
                 @click="tab=1"
            >
                {{$t('customer.review.title')}}
            </div>
        </div>
        <BaseContainer class="feed-back">
            <cart-item-messages v-if="tab===0" :request-headers="requestHeaders" :item="item"/>
            <cart-item-review v-if="tab===1" v-model="item.review" :request-headers="requestHeaders"/>
        </BaseContainer>
    </div>
</template>

<script>
    import BaseContainer from "./../component/BaseContainer";
    import Notification from "./../component/Notification";
    import CartItemReview from "./../component/CartItemReview";
    import CartItemMessages from "./../component/CartItemMessages";

    export default {
        components: {
            BaseContainer,
            Notification,
            CartItemReview,
            CartItemMessages,
        },
        props: {
            item: {
                type: Object,
                required: true,
            },
            requestHeaders: {
                type: Object,
                default: () => {
                    return {}
                },
            },
        },
        data() {
            return {
                tab: 0,
            }
        }
    };
</script>

<style lang="scss" scoped>
    .product-page {
        color: #1C1C1C;
    }

    .detailedProductInfo {
        .good {
            margin-bottom: 5px;
        }

        .good:last-child {
            margin-bottom: 0;
        }

        .title {
            padding: 0 0 20px;
            display: flex;
            justify-content: space-between;
            font-weight: 500;

            .back {
                color: #CCCCCC;
                cursor: pointer;
                transition: .3s;

                &:hover {
                    color: #1C1C1C;
                }

                &:active {
                    transform: scale(0.9)
                }
            }
        }
    }

    .detailedProductInfoDetails {
        margin: 30px 0;

        .title {
            font-size: 15px;
            font-weight: 500;
        }

        &__listItem {
            margin: 5px 0;
            padding: 10px 15px;
            background: #FAFAFA;
            border-radius: 8px;

            color: #A8A8A8;
            font-weight: 500;

            &__black {
                color: #1C1C1C;
                font-weight: 400;
            }

            a {
                color: #0177FD;
                font-weight: 400;
            }

            .item-icon {
                color: #1C1C1C;
                font-size: 20px;
                padding-right: 20px;
            }

            .contacts {
                padding-top: 10px;
                padding-left: 38px;

                @media screen and (max-width: 600px) {
                    padding: 10px 0;
                }

                i {
                    color: #0177FD;
                }

                .padding-left {
                    padding-left: 7px;

                    @media screen and (max-width: 600px) {
                        padding: 0;
                    }
                }
            }
        }
    }

    .detailedProductTabs {
        margin: 30px 0 0;

        @media screen and (max-width: 600px) {
            display: flex;
        }

        &__item {
            display: inline-block;
            background: #FAFAFA;
            padding: 15px 20px;
            transition: .2s;
            font-weight: 500;
            cursor: pointer;

            @media screen and (max-width: 600px) {
                text-align: center;

            }

            &:first-of-type {
                border-top-left-radius: 8px;
            }

            &:last-of-type {
                border-top-right-radius: 8px;
            }

            &:hover {
                transform: translateY(-5px);
            }

            &__active {

                background: #F0F0F0;

                &:hover {
                    transform: translateY(0);
                }
            }
        }
    }

    .response {
        .info {
            margin: 0;
            font-size: 14px;
            line-height: 20px;


            a {
                text-decoration: none;
            }

            .yellow {
                color: #FFB367;

            }

            .blue {
                color: #0177FD;
            }
        }

        .rate {
            margin-top: 30px;
            font-size: 15px;
            font-weight: 500;

            .good {
                color: #4CD09E;
                line-height: 100%;
                cursor: pointer;
            }

            .bad {
                margin-left: 20px;
                color: #A8A8A8;
                line-height: 100%;
                cursor: pointer;
            }
        }
    }

    .response_done {
        .details {
            font-size: 15px;
            font-weight: 500;
            color: #A8A8A8;
            display: flex;
            justify-content: space-between;

            @media screen and (max-width: 600px) {
                flex-direction: column;
                align-items: center;
                text-align: center;
                margin: 0 0 30px;
            }

            i {
                padding-right: 10px;
            }

            .response-type {
                padding-left: 20px;
            }


            .black {
                color: #1C1C1C;
                font-weight: 400;
            }

            .green {
                color: #4CD09E;
            }
        }

        .date {
            @media screen and (max-width: 600px) {
                margin-top: 10px;
            }
        }

        .body {
            background: #4CD09E;
            padding: 15px 14px;
            border-radius: 8px;
            color: #FFFFFF;

            @media screen and (max-width: 600px) {
                text-align: center;
            }

            i {
                font-size: 20px;
            }

            span {
                padding-left: 15px;
                font-weight: 500;
            }
        }

        .options {
            margin: 16px 0 0;
            text-align: right;
            color: #A8A8A8;
            font-size: 14px;

            @media screen and (max-width: 600px) {
                margin-top: 30px;
            }

            @media screen and (max-width: 600px) {
                text-align: center;
            }

            i {
                padding-right: 10px;
            }

            .delete {
                margin-right: 20px;
                color: #FF6060;
            }

            .delete, .edit {
                cursor: pointer;
            }
        }
    }
</style>
