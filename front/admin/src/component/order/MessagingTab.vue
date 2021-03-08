<template>
    <div class="orderMessagingTab">
        <card-loading v-if="!loaded" no-card/>
        <div v-else>
            <ticket-message v-for="(item,i) in items" :key="i"
                            :text="item.text"
                            :answerUserName="item.isAnswer ? 'Вы' : 'Покупатель'"
                            :createdTs="item.createdDate"
                            :isAnswer="item.isAnswer"
            />
            <v-container>
                <v-form @submit.prevent="form.submit" style="display:contents">
                    <v-flex xs10 offset-xs1>
                        <v-textarea v-model="form.data.text"
                                    :error-messages="form.errors.text"
                                    label="Сообщение"
                                    outlined
                        />
                    </v-flex>
                    <v-flex xs10 offset-xs1 orderMessagingTab__flexContainer>
                        <div>
                            <v-btn type="submit" :disabled="form.loading" color="primary">Отправить</v-btn>
                        </div>
                        <message-email-notification v-model="isSellerNotificationsDisabled"/>
                    </v-flex>
                </v-form>
            </v-container>
        </div>
    </div>
</template>

<script>
    import CardLoading from "../../component/CardLoading";
    import TicketMessage from "../../component/support/TicketMessage";
    import MessageEmailNotification from "./MessageEmailNotification";

    export default {
        components: {MessageEmailNotification, CardLoading, TicketMessage},
        props: {},
        data() {
            return {
                loaded: false,
                items: [],
                form: null,
                listing: null,
                isSellerNotificationsDisabled: null,
            }
        },
        created() {
            this.listing = this.$ewll.initListingForm(this, {
                url: '/crud/cartItemMessage',
                itemsPerPage: 50, //@TODO !!!!
                filters: {cartItemId: this.$route.params.id},
                success: function (response) {
                    this.items = response.body.items;
                    this.isSellerNotificationsDisabled = response.body.extra.isSellerNotificationsDisabled;
                    this.loaded = true;
                }.bind(this)
            });
            this.form = this.$ewll.initForm(this, {
                url: '/crud/cartItemMessage',
                data: {cartItemId: this.$route.params.id},
                success: function(){
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
    }
</script>

<style lang="scss">
    .orderMessagingTab {
        &__flexContainer {
            display: flex;

            @media screen and (max-width: 577px) {
                flex-direction: column;
            }
         }
    }
</style>
