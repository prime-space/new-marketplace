<template>
    <div class="cartItemReview">
        <div v-if="review" class="cartItemReview__view response_done">
            <div class="cartItemReview__view__head">
                <div v-if="review.isGood">
                    <v-icon color="success" left dense>mdi-thumb-up</v-icon>
                    <span class="success--text">{{$t('customer.review.good')}}</span>
                </div>
                <div v-else>
                    <v-icon color="error" left dense>mdi-thumb-down</v-icon>
                    <span class="error--text">{{$t('customer.review.bad')}}</span>
                </div>
                <div>
                    {{review.dateCreate}}
                </div>
            </div>
            <div class="cartItemReview__view__body"
                 :style="[{'background-color': review.isGood ? '#4CD09E' : '#ff5252'}]"
            >
                <span>{{review.text}}</span>
            </div>
            <div v-if="review && review.answer" class="cartItemReview__view__answer">
                <span class="cartItemReview__view__answer__arrow"><i class="mdi mdi-share"></i></span>
                <span class="cartItemReview__view__answer__role">{{$t('customer.review.sellerAnswer')}}</span>
                <span class="cartItemReview__view__answer__text">{{review.answer}}</span>
            </div>
            <div class="cartItemReview__view__actions">
                <v-btn color="error" @click="remove" :loading="removeForm.loading" text small>
                    <v-icon small left>mdi-delete</v-icon>
                    {{$t('customer.review.delete')}}
                </v-btn>
                <v-btn @click="initEditing" text small>
                    <v-icon small left>mdi-pencil</v-icon>
                    {{$t('customer.review.edit')}}
                </v-btn>
            </div>
        </div>
        <cart-item-review-form v-else-if="mode==='create'" :form="createForm"/>
        <cart-item-review-form v-else-if="mode==='update'" :form="updateForm"/>
    </div>
</template>

<script>
    import CartItemReviewForm from './CartItemReviewForm';

    export default {
        components: {CartItemReviewForm},
        props: {
            requestHeaders: Object,
            value: Object,
        },
        data() {
            return {
                createForm: null,
                updateForm: null,
                removeForm: null,
                review: null,
                mode: 'create',
            };
        },
        created() {
            this.createForm = Main.initForm(this, {
                headers: this.requestHeaders,
                url: '/crud/customerCartItemReview',
                data: {cartItemId: this.$route.params.cartItemId, isGood: 1},
                snackSuccessMessage: this.$t('customer.review.published'),
                success: function (response) {
                    this.review = response.body.extra.review;
                }.bind(this),
            });
            this.updateForm = Main.initForm(this, {
                headers: this.requestHeaders,
                snackSuccessMessage: this.$t('customer.review.edited'),
                success: function (response) {
                    this.review = response.body.extra.review;
                }.bind(this),
            });
            this.removeForm = Main.initForm(this, {
                method: 'delete',
                headers: this.requestHeaders,
                data: {cartItemId: this.$route.params.cartItemId},
                isFormHandleValidationErrors: false,
                snackSuccessMessage: this.$t('customer.review.deleted'),
                success: function () {
                    this.mode = 'create';
                    //@TODO reset form
                    this.createForm.data = {cartItemId: this.$route.params.cartItemId, isGood: 1};
                    this.updateForm.data = {};
                    this.review = null;
                }.bind(this),
            });
            this.review = this.value;
        },
        watch: {
            review: {
                handler: function (value) {
                    this.$emit('input', value);
                },
                deep: true
            }
        },
        methods: {
            remove() {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Удалить отзыв',
                    body: 'Уверены, что хотите удалить отзыв?',
                }).then(confirmation => {
                    if (confirmation) {
                        this.removeForm.submit({url: '/crud/customerCartItemReview/' + this.review.id});
                    }
                });
            },
            initEditing() {
                this.mode = 'update';
                this.updateForm.data = {
                    cartItemId: this.$route.params.cartItemId,
                    text: this.review.text,
                    isGood: this.review.isGood - 0,
                };
                this.updateForm.url = '/crud/customerCartItemReview/' + this.review.id;
                this.review = null;
            },
        },
    };
</script>

<style lang="scss">
    .cartItemReview {
        &__view {
            &__head {
                font-size: 15px;
                font-weight: 500;
                color: #A8A8A8;
                display: flex;
                justify-content: space-between;
                padding-bottom: 5px;
            }

            &__body {
                padding: 15px 14px;
                border-radius: 8px;
                color: #FFFFFF;
                white-space: pre-wrap;
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

            &__actions {
                margin: 16px 0 0;
                text-align: right;
                font-size: 14px;

                i {
                    padding-right: 5px;
                }

                &__delete {
                    margin-right: 20px;
                    color: #FF6060;
                }

                &__delete, &__edit {
                    cursor: pointer;
                }
            }
        }
    }
</style>
