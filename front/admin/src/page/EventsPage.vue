<template>
    <div class="eventsPage">
        <v-data-table
                :headers="headers"
                :items="items"
                :items-per-page="listing.pagination.itemsPerPage"
                :loading="listing.loading"
                :mobile-breakpoint="100"
                class="eventsPage__table elevation-1"
                hide-default-footer
        >
            <template v-slot:top>
                <v-toolbar flat dense>
                    <v-spacer/>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" small disabled tile icon>
                                <v-icon>mdi-filter</v-icon>
                            </v-btn>
                        </template>
                        <span>Фильтры</span>
                    </v-tooltip>
                    <icon-button-with-progress
                            @action="markAllReadForm.submit"
                            :loading="markAllReadForm.loading"
                            icon="mdi-read"
                            tooltip="Отметить все просмотренными"
                            small
                    />
                </v-toolbar>
            </template>
            <template slot="item" slot-scope="props">
                <tr :class="[{'listTable__row__highlight': !props.item.isRead}, 'eventsPage__row']">
                    <td>{{ props.item.dateCreate }}</td>
                    <td>
                        <span v-if="props.item.typeId===1">
                            Товар <span class="font-weight-bold">{{props.item.data.productName}} </span> <span
                                class="green--text">принят</span>.
                        </span>
                        <span v-if="props.item.typeId===2">
                            Товар <span class="font-weight-bold">{{props.item.data.productName}} </span> <span
                                class="red--text">отклонен</span>.
                        </span>
                        <span v-if="props.item.typeId===3">
                            Поступило предложение о партнерстве от пользователя <span class="font-weight-bold">{{props.item.data.sellerName}}</span>.
                        </span>
                        <span v-if="props.item.typeId===4">
                            Пользователь <span class="font-weight-bold">{{props.item.data.agentName}}</span> принял ваше предложение о партнерстве.
                        </span>
                        <span v-if="props.item.typeId===5">
                            Пользователь <span class="font-weight-bold">{{props.item.data.userName}}</span>
                            <span class="red--text"> расторг</span> с вами партнерские отношения.
                        </span>
                        <span v-if="props.item.typeId===6">
                            Новая продажа
                        </span>
                        <span v-if="props.item.typeId===7">
                            Новая партнерская продажа
                        </span>
                        <span v-if="props.item.typeId===8">
                            Выплата <span class="bold">#{{props.item.referenceId}}</span> завершилась неудачей. Средства возвращены на баланс.
                            Проверьте, что получатель может принять перевод и повторите попытку.
                        </span>
                        <span v-if="props.item.typeId===9">
                            Новый ответ по вашему запросу <span class="bold">{{props.item.data.ticketSubject}}</span>
                        </span>
                        <span v-if="props.item.typeId===10">
                            Новое сообщение от покупателя
                        </span>
                        <span v-if="props.item.typeId===11">
                            Покупатель оставил отзыв
                        </span>
                        <span v-if="props.item.typeId===12">
                            Товар <span class="font-weight-bold">{{props.item.data.productName}} </span> <span
                                class="red--text">заблокирован</span>.
                        </span>
                        <span v-if="props.item.typeId===13">
                            Товар <span class="font-weight-bold">{{props.item.data.productName}} </span> <span
                                class="green--text">разблокирован</span>.
                        </span>
                    </td>
                    <td>
                        <v-layout justify-end>
                            <icon-button-with-progress v-if="[1,2,3,4,6,7,8,9,10,11,12,13].includes(props.item.typeId)"
                                                       @action="markRead(props.item)"
                                                       :href="compileReferenceLink(props.item)"
                                                       target="_blank"
                                                       :loading="markingReadForm.loading"
                                                       :progressColor="markingReadId === props.item.id? 'primary' : 'grey'"
                                                       icon="mdi-arrow-right-bold-circle-outline"
                                                       tooltip="Открыть в новой вкладке"
                            />
                            <icon-button-with-progress v-else-if="!props.item.isRead"
                                                       @action="markRead(props.item)"
                                                       :loading="markingReadForm.loading"
                                                       :progressColor="markingReadId === props.item.id? 'primary' : 'grey'"
                                                       icon="mdi-check"
                                                       tooltip="Отметить прочитанным"
                            />
                        </v-layout>
                    </td>
                </tr>
            </template>
        </v-data-table>
        <div class="text-center pt-2">
            <v-pagination
                    v-if="listing.hasElements"
                    v-model="listing.pagination.page"
                    :length="listing.pagination.pageCount"
                    @input="loadItems"
                    :key="listing.pagination.componentKey"
            />
        </div>
    </div>
</template>

<script>
    import {page} from './../mixin/page';
    import IconButtonWithProgress from './../component/IconButtonWithProgress';

    export default {
        mixins: [page],
        components: {IconButtonWithProgress},
        data() {
            return {
                config: config,
                headers: [
                    {text: 'Дата', sortable: false},
                    {text: 'Информация', sortable: false},
                    {text: '', sortable: false},
                ],
                items: [],
                listing: null,
                markAllReadForm: null,
                markingReadForm: null,
                markingReadId: null,
            }
        },
        created() {
            this.listing = this.$ewll.initListingForm(this, {
                url: '/crud/event',
                sort: {id: 'desc'},
                success: function (response) {
                    this.items = response.body.items;
                }.bind(this),
            });
            this.markAllReadForm = this.$ewll.initForm(this, {
                url: '/crud/event/markAllAsRead',
                isFormHandleValidationErrors: false,
                success: function () {
                    this.config.haveUnreadEvent = false;
                    this.loadItems();
                }.bind(this),
            });
            this.markingReadForm = this.$ewll.initForm(this, {
                isFormHandleValidationErrors: false,
            });
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
                this.loadItems();
            },
            loadItems() {
                this.items = [];
                this.listing.submit();
            },
            compileReferenceLink(event) {
                let routeData = null;
                if ([1, 2, 12, 13].includes(event.typeId)) {
                    routeData = this.$router.resolve({name: 'productEdit', params: {id: event.referenceId}});
                } else if (3 === event.typeId) {
                    routeData = this.$router.resolve({name: 'partnershipAgentSellers', hash: '#offers'});
                } else if (4 === event.typeId) {
                    routeData = this.$router.resolve({
                        name: 'partnershipSellerAgent',
                        params: {id: event.referenceId}
                    });
                } else if (6 === event.typeId) {
                    routeData = this.$router.resolve({name: 'order', params: {id: event.referenceId}});
                } else if (7 === event.typeId) {
                    routeData = this.$router.resolve({name: 'dashboard',});
                } else if (8 === event.typeId) {
                    routeData = this.$router.resolve({name: 'payouts',});
                } else if (9 === event.typeId) {
                    routeData = this.$router.resolve({name: 'supportTicket', params: {ticketId: event.referenceId}});
                } else if (10 === event.typeId) {
                    routeData = this.$router.resolve({name: 'order', params: {id: event.referenceId}, hash: '#messaging'});
                } else if (11 === event.typeId) {
                    routeData = this.$router.resolve({name: 'order', params: {id: event.referenceId}, hash: '#review'});
                } else {
                    //@TODO Sentry
                    return;
                }

                return routeData.href;
            },
            markRead(event, success = function () {
            }) {
                this.markingReadId = event.id;
                this.markingReadForm.submit({
                    url: '/crud/event/' + event.id + '/markAsRead',
                    success: function (response) {
                        this.config.haveUnreadEvent = response.body.haveUnreadEvent;
                        this.items.filter(function (item) {
                            if (item.id === event.id) {
                                item.isRead = true;
                            }
                        }.bind(this));
                        success();
                    }.bind(this),
                });
            },
        }
    }
</script>
