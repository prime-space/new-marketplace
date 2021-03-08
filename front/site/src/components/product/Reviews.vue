<template>
    <div class="productReviews">
        <div class="productReviews__head">
            Отзывы покупателей
            <span v-if="total" class="productReviews__head__counter">{{total}}</span>
        </div>
        <div class="boxContainer">
            <template v-if="skeleton">
                <v-sheet v-for="i in 5" :key="i" :color="`grey lighten-4`">
                    <v-skeleton-loader width="100%" type="list-item-two-line"/>
                </v-sheet>
            </template>
            <template v-else-if="items.length===0">
                <div class="text-center text--disabled">Отзывов пока нет</div>
            </template>
            <template v-else>
                <div class="productReviews__items">
                    <div class="productReviews__items__item" v-for="(review, i) in items" :key="i">
                        <div class="productReviews__items__item__header">
                        <span class="productReviews__items__item__header__left">
                            <i v-if="review.isGood" class="mdi mdi-thumb-up" style="color:#16d67d"/>
                            <i v-else class="mdi mdi-thumb-down" style="color:#ff5050"/>
                            <span class="productReviews__items__item__header__left__role">Покупатель</span>
                        </span>
                            <span class="productReviews__items__item__header__date">{{review.dateCreate}}</span>
                        </div>
                        <div :class="['productReviews__items__item__body', {'productReviews__items__item__body--bad': !review.isGood}]">
                            <span>{{ review.text }}</span>
                        </div>
                        <div v-if="review.answer" class="productReviews__items__item__answer">
                            <span class="productReviews__items__item__answer__arrow"><i
                                    class="mdi mdi-share"></i></span>
                            <span class="productReviews__items__item__answer__role">Продавец</span>
                            <span class="productReviews__items__item__answer__text">{{ review.answer }}</span>
                        </div>
                    </div>
                </div>
                <v-btn v-if="!noHaveMore"
                       @click="loadMore"
                       class="productReviews__showMore"
                       color="#e9e9e9"
                       elevation="0"
                       :loading="listing.loading"
                       x-large
                >
                    <span>Показать больше</span>
                </v-btn>
            </template>
        </div>

    </div>
</template>

<script>
    export default {
        props: {
            productId: Number,
        },
        data() {
            return {
                config: config,
                skeleton: true,
                listing: null,
                noHaveMore: false,
                items: [],
                total: 0,
            };
        },
        created() {
            let itemsPerPage = 5;
            this.listing = Main.initListingForm(this, {
                url: '/crud/siteProductReview',
                itemsPerPage: itemsPerPage,
                sort: {id: 'desc'},
                filters: {productId: this.productId},
                success: function (response) {
                    let items = response.body.items;
                    if (items.length < itemsPerPage) {
                        this.noHaveMore = 1;
                    }
                    this.items = [...this.items, ...items];
                    this.total = response.body.total;
                    this.skeleton = false;
                }.bind(this),
            });
        },
        mounted() {
            this.init();
        },
        methods: {
            init() {
                this.loadItems();
            },
            loadItems() {
                this.listing.submit();
            },
            loadMore() {
                this.listing.pagination.page++;
                this.loadItems();
            },
        },
    }
</script>

<style lang="scss">
    .productReviews {
        &__head {
            background: #f3f3f3;
            border-radius: 8px 8px 0 0;
            padding: 11px 20px;
            font-size: 16px;
            border-bottom: 2px solid #2b7bfe;
            transition: 0.3s;
            display: inline-block;

            &__counter {
                margin-left: 10px;
                border-radius: 4px;
                background: #2b7bfe;
                color: #fff;
                font-size: 13px;
                font-weight: 500;
                text-align: center;
                padding: 5px;
            }
        }

        &__items {
            &__item {
                &__header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 10px 0 3px 0;

                    &__left {
                        display: flex;
                        align-items: center;

                        &__role {
                            font-size: 13px;
                            color: rgba(#1c1c1c, 0.3);
                            font-weight: 500;
                        }
                    }

                    &__date {
                        font-weight: 500;
                        font-size: 13px;
                        color: rgba(#1c1c1c, 0.3);
                    }

                    i {
                        font-size: 18px;
                        margin: 3px 7px 0 0;
                    }
                }

                &__body {
                    border-radius: 8px;
                    color: #1c1c1c;
                    font-size: 14px;
                    padding: 15px 20px;
                    white-space: pre-wrap;
                    background: rgba(#16d67d, 0.2);

                    &--bad {
                        background: rgba(#ff2a2a, 0.2);
                    }
                }

                &__answer {
                    position: relative;
                    background: #f3f3f3;
                    border-radius: 0 0 8px 8px;
                    padding: 10px 20px;
                    margin-left: 20px;

                    span {
                        display: block;
                    }

                    &__role {
                        font-weight: 500;
                        font-size: 11px;
                        text-align: left;
                        color: #1c1c1c;
                        opacity: 0.3;
                    }

                    &__text {
                        padding: 5px 0 0;
                        font-weight: normal;
                        font-size: 14px;
                        text-align: left;
                        color: #1c1c1c;
                        white-space: pre-wrap;
                    }

                    &__arrow {
                        position: absolute;
                        left: 0;
                        top: 0;
                        font-size: 18px;
                        color: #b1b1b1;
                        transform: scale(1, -1) translateX(-100%);
                    }
                }
            }
        }

        &__showMore {
            width: 100%;
            color: #555 !important;
            margin-top: 15px;
        }
    }
</style>
