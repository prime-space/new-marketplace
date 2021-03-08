<template>
    <div class="orderMessageEmailNotification">
        <div class="text-caption">
            <span v-if="value">
            Вы отписаны от уведомлений о новых сообщениях по этому заказу посредством email.
            </span>
            <span v-else>
            Вы подписаны на уведомления о новых сообщениях по этому заказу посредством email.
            </span>
        </div>
        <div>
            <v-btn small tile @click="subscribe" :loading="subscribeForm.loading">
                <template v-if="value">
                    <v-icon small left color="success">mdi-email-plus</v-icon> Подписаться
                </template>
                <template v-else>
                    <v-icon small left color="red">mdi-email-minus</v-icon> Отписаться
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
            config: config,
            subscribeForm: null,
        }),
        created() {
            this.subscribeForm = this.$ewll.initForm(this, {
                method: 'post',
                url: '/crud/sellerCartItemMessageNotification/' + this.$route.params.id,
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
                        isSellerNotificationsDisabled: Number(!this.value)
                    }
                });
            },
        }
    }
</script>

<style lang="scss">
    .orderMessageEmailNotification {
        margin-left: 20px;
        display: flex;
        flex-direction: column;
        flex: 1;

        div {
            display: flex;
            justify-content: flex-end;
            text-align: end;
            margin-bottom: 10px;
        }

        @media screen and (max-width: 577px) {
            margin-left: 0;
            margin-top: 30px;

            div {
                justify-content: flex-start;
                text-align: start;
            }
        }
    }
</style>
