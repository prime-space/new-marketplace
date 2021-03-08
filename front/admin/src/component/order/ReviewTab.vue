<template>
    <div class="orderPageReviewTab">
        <card-loading v-if="!loaded" no-card/>
        <v-card v-else>
            <v-card-title class="headline">Отзыв покупателя</v-card-title>
            <v-card-text>
                <div v-if="review">
                    <v-alert style="white-space:pre"
                             border="left"
                             :type="review.isGood ? 'success' : 'error'"
                             :icon="review.isGood ? 'mdi-thumb-up' : 'mdi-thumb-down'"
                    >
                        <span>{{review.text}}</span>
                    </v-alert>
                    <div class="">
                        <h2>Ваш ответ</h2>
                        <v-alert v-if="review.answer"
                                 style="margin-top:10px;white-space:pre;color:"
                                 border="left"
                                 color="#dadada"
                        >
                            <span>{{review.answer}}</span>
                        </v-alert>
                        <v-container v-else>
                            <v-form @submit.prevent="form.submit" style="display:contents">
                                <v-flex xs10 offset-xs1>
                                    <v-textarea v-model="form.data.answer"
                                                :error-messages="form.errors.answer"
                                                label="Ваш ответ"
                                                outlined
                                    />
                                </v-flex>
                                <v-flex xs10 offset-xs1>
                                    <v-btn type="submit" :loading="form.loading" color="primary">Ответить</v-btn>
                                </v-flex>
                            </v-form>
                        </v-container>
                    </div>
                </div>
                <div v-else>
                    <v-alert style="margin-bottom:0" border="left" type="info">
                        Покупатель пока не оставил отзыв.
                    </v-alert>
                </div>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
    import CardLoading from "../../component/CardLoading";

    export default {
        components: {CardLoading,},
        props: {},
        data() {
            return {
                loaded: false,
                form: null,
                review: null,
            }
        },
        created() {
            this.$ewll.initListingForm(this, {
                url: '/crud/sellerReview',
                filters: {cartItemId: this.$route.params.id},
                success: function (response) {
                    if (response.body.items.length === 1) {
                        this.review = response.body.items[0];
                        this.form = this.$ewll.initForm(this, {
                            url: '/crud/sellerReview/' + this.review.id,
                            success: function (response) {
                                this.review = response.body.extra.review;
                            }.bind(this),
                        });
                    }
                    this.loaded = true;
                }.bind(this)
            }).submit();
        },
        mounted() {
            this.init();
        },
        methods: {
            init() {
            },
        },
    }
</script>

<style type="scss">
    .orderPageReviewTab {
    }
</style>
