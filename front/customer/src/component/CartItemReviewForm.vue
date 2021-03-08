<template>
    <div class="cartItemReviewForm">
        <div>{{$t('customer.review.public')}}</div>
        <div>{{$t('customer.review.timeLimit')}}</div>
        <v-radio-group v-model="form.data.isGood" :error-messages="form.errors.isGood" row dense>
            <v-radio :value="1" color="success" class="">
                <template v-slot:label>
                    <v-icon color="success" left dense>mdi-thumb-up</v-icon>
                    <span class="success--text">{{$t('customer.review.good')}}</span>
                </template>
            </v-radio>
            <v-radio :value="0" color="error" class="">
                <template v-slot:label>
                    <v-icon color="error" left dense>mdi-thumb-down</v-icon>
                    <span class="error--text">{{$t('customer.review.bad')}}</span>
                </template>
            </v-radio>
        </v-radio-group>
        <div>
            <v-textarea v-model="form.data.text"
                        :error="form.getError('text')"
                        :label="$t('customer.review.text')"
                        rows="3"
                        filled
            />
        </div>
        <form-error :error="form.getError('form')"/>
        <div class="cartItemReviewForm__submit">
            <BaseButton @click="form.submit"
                        :disabled="form.loading"
                        class="cartItemReviewForm__submit__button"
                        background="#0177FD"
            >
                <i class="mdi mdi-telegram"></i> {{$t('customer.review.send')}}
            </BaseButton>
        </div>
    </div>
</template>

<script>
    import BaseButton from "./BaseButton";
    import FormError from './../component/FormError.vue';

    export default {
        components: {BaseButton, FormError},
        props: {
            form: Object
        },
        data() {
            return {};
        },
    };
</script>

<style lang="scss">
    .cartItemReviewForm {
        &__submit {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;

            @media screen and (max-width: 600px) {
                flex-direction: column;
                align-items: center;
            }

            &__button {
                font-size: 16px;
                max-width: 288px;
            }
        }
    }
</style>
