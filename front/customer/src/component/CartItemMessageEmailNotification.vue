<template>
    <div class="cartItemMessageEmailNotification">
        <div class="text-caption">
            <span v-if="value">
                {{$t('customer.cartItemEmailNotification.subscribe.text')}}
            </span>
            <span v-else>
                {{$t('customer.cartItemEmailNotification.unsubscribe.text')}}
            </span>
        </div>
        <div>
            <v-btn small tile @click="subscribe" :loading="subscribeForm.loading">
                <template v-if="value">
                    <v-icon small left color="success">mdi-email-plus</v-icon>
                    {{$t('customer.cartItemEmailNotification.subscribe.button')}}
                </template>
                <template v-else>
                    <v-icon small left color="red">mdi-email-minus</v-icon>
                    {{$t('customer.cartItemEmailNotification.unsubscribe.button')}}
                </template>
            </v-btn>
        </div>
    </div>
</template>

<script>

    //@TODO need to transpose to shared component
    export default {
        props: {
            value: Boolean,
        },
        data: () => ({
            subscribeForm: null,
        }),
        created() {
            this.subscribeForm = Main.initForm(this, {
                method: 'post',
                url: '/crud/customerCartItemMessageNotification/' + this.$route.params.cartItemId,
                isFormHandleValidationErrors: false,
                success: function () {
                    this.$emit('input', !this.value);
                }.bind(this),
            });
        },
        methods: {
            subscribe() {
                this.subscribeForm.submit({
                    data: {
                        isCustomerNotificationsDisabled: Number(!this.value)
                    }
                });
            },
        }
    }
</script>

<style lang="scss">
    .cartItemMessageEmailNotification {
        display: flex;
        flex-direction: column;
        margin-right: 20px;

        div {
            margin-bottom: 10px;
        }

        @media screen and (max-width: 600px) {
            order:2;
            margin-top: 30px;
        }
    }
</style>
