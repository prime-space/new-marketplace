<template>
    <div class="supportPage">
        <card-loading v-if="!loaded"/>
        <div v-else>
            <ticket-message v-for="(item,i) in items" :key="i"
                            :text="item.text"
                            :answerUserName="item.answerUserName"
                            :createdTs="item.createdTs"
                            :isAnswer="item.isAnswer"
            />
            <div v-if="item.messagesSendingNum>0">
                <ticket-message v-for="n in item.messagesSendingNum" :key="n" isSending/>
            </div>
            <v-container>
                <v-form @submit.prevent="create" style="display:contents">
                    <v-flex xs10 offset-xs1>
                        <v-textarea v-model="createForm.data.message"
                                    :error-messages="createForm.errors.message"
                                    label="Сообщение"
                                    outlined
                        />
                    </v-flex>
                    <v-flex xs10 offset-xs1>
                        <v-btn type="submit" :disabled="createForm.loading" color="primary">Отправить</v-btn>
                    </v-flex>
                </v-form>
            </v-container>
        </div>
    </div>
</template>

<script>
    import {page} from './../mixin/page';
    import CardLoading from "./../component/CardLoading";
    import TicketMessage from "../component/support/TicketMessage";

    export default {
        mixins: [page],
        components: {TicketMessage, CardLoading,},
        data() {
            return {
                item: null,
                itemForm: null,
                createForm: null,
                listing: null,
                items: [],
                loaded: false,
                timer: null,
            }
        },
        created() {
            this.itemForm = this.$ewll.initForm(this, {
                method: 'get',
                url: '/crud/ticket/' + this.$route.params.ticketId,
                success: function (response) {
                    this.item = response.body;
                    if (this.item.messagesNum > this.item.messagesSendingNum) {
                        this.loadItems();
                    } else {
                        this.loaded = true;
                        this.setTimeout(this.itemForm.submit, 3000);
                    }
                }.bind(this),
                error: function () {
                    this.setTimeout(this.itemForm.submit, 3000);
                }.bind(this)
            });
            this.listing = this.$ewll.initListingForm(this, {
                url: '/crud/ticketMessage',
                filters: {ticketId: this.$route.params.ticketId},
                itemsPerPage: 50, //@TODO !!!!
                success: function (response) {
                    this.items = response.body.items;
                    if (undefined !== response.body.extra.messagesSendingNum) {
                        this.item.messagesSendingNum = response.body.extra.messagesSendingNum;
                    }
                    this.loaded = true;
                    let timeout = this.item.messagesSendingNum > 0 ? 3000 : 10000;
                    this.setTimeout(this.loadItems, timeout)
                }.bind(this),
                error: function () {
                    this.setTimeout(this.loadItems, 3000);
                }.bind(this)
            });
            this.createForm = this.$ewll.initForm(this, {
                url: `/crud/ticketMessage`,
                success: function () {
                    this.item.messagesSendingNum++;
                    this.setTimeout(this.loadItems, 4000);
                }.bind(this),
            });
        },
        mounted() {
            this.init();
            // this.loadItems();
        },
        beforeDestroy() {
            clearTimeout(this.timer);
        },
        methods: {
            init() {
                this.itemForm.submit();
            },
            loadItems() {
                this.listing.submit();
            },
            create() {
                this.createForm.submit({data: {ticketId: this.$route.params.ticketId}});
            },
            setTimeout(method, time) {
                clearTimeout(this.timer);
                this.timer = setTimeout(method, time);
            },
        }
    }
</script>
