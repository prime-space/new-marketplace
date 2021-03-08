<template>
    <div class="cartItemMessages">
        <cart-item-message v-for="(item, i) in items"
                           :key="i"
                           :position="item.isAnswer ? 'left' : 'right'"
                           :text="item.text"
                           :time="item.createdDate"
                           :author="item.isAnswer ? $t('customer.sellerMessaging.seller') : $t('customer.sellerMessaging.you')"
        />
        <div style="margin-top:45px;">
            <v-textarea v-model="form.data.text"
                        :error="form.getError('text')"
                        :label="$t('customer.sellerMessaging.messageText')"
                        rows="3"
                        filled
            />
        </div>
        <form-error :error="form.getError('form')"/>
        <div class="cartItemMessages__submit">
            <cart-item-message-email-notification v-model="item.isCustomerNotificationsDisabled"/>
            <BaseButton @click="form.submit"
                        :disabled="form.loading"
                        class="cartItemMessages__submitButton"
                        background="#0177FD"
            >
                <i class="mdi mdi-telegram"></i> {{$t('customer.sellerMessaging.send')}}
            </BaseButton>
        </div>
    </div>
</template>

<script>
    import BaseButton from "./BaseButton";
    import CartItemMessage from "./CartItemMessage";
    import FormError from './../component/FormError.vue';
    import CartItemMessageEmailNotification from "./CartItemMessageEmailNotification";

    export default {
        components: {BaseButton, CartItemMessage, FormError, CartItemMessageEmailNotification},
        props: {
            requestHeaders: Object,
            item: {
                type: Object,
                required: true,
            },
        },
        data() {
            return {
                listing: null,
                form: null,
                items: [],
            };
        },
        created() {
            this.listing = Main.initListingForm(this, {
                headers: this.requestHeaders,
                itemsPerPage: 50, //@TODO !!!!
                filters: {cartItemId: this.$route.params.cartItemId},
                url: '/crud/customerCartItemMessage',
                sort: {id: 'asc'},
                success: function (response) {
                    this.items = response.body.items;
                }.bind(this),
            });
            this.form = Main.initForm(this, {
                headers: this.requestHeaders,
                url: '/crud/customerCartItemMessage',
                data: {cartItemId: this.$route.params.cartItemId},
                success: function () {
                    this.form.data.text = '';
                    this.loadItems();
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
        },
    };
</script>

<style lang="scss">
    .cartItemMessages {
        border-top-left-radius: 0;

        @media screen and (max-width: 600px) {
            border-top-right-radius: 0;
        }

        &__submit {
            display: flex;
            justify-content: space-between;

            @media screen and (max-width: 600px) {
                flex-direction: column;
                align-items: center;
            }
        }

        &__submitButton {
            max-width: 288px;
            max-height: 50px;
            font-size: 16px;
        }
    }
</style>
