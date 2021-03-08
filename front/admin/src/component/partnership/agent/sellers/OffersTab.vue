<template>
    <div class="partnershipAgentSellersTab">
        <v-data-table
                :headers="headers"
                :items="items"
                :items-per-page="listing.pagination.itemsPerPage"
                :loading="listing.loading"
                :mobile-breakpoint="100"
                class="list__table elevation-1"
                hide-default-footer
        >
            <template v-slot:top>
                <v-toolbar flat dense>
                    <span class="subtitle-1">Предложения от продавцов</span>
                    <v-spacer style="margin-left:15px"/>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" small disabled tile icon>
                                <v-icon>mdi-filter</v-icon>
                            </v-btn>
                        </template>
                        <span>Фильтры</span>
                    </v-tooltip>
                </v-toolbar>
            </template>
            <template slot="item" slot-scope="props">
                <tr class="listTable__row">
                    <td>{{ props.item.sellerName }}</td>
                    <td class="text-center">{{ props.item.fee }}%</td>
                    <td>
                        <v-layout justify-end>
                            <icon-button-with-progress
                                    @action="reject(props.item)"
                                    :loading="rejectForm.loading"
                                    :progressColor="rejectingId === props.item.id? 'primary' : 'grey'"
                                    icon="mdi-cancel"
                                    tooltip="Отклонить"
                            />
                            <icon-button-with-progress
                                    @action="accept(props.item)"
                                    :loading="acceptForm.loading"
                                    :progressColor="acceptingId === props.item.id? 'primary' : 'grey'"
                                    icon="mdi-check"
                                    tooltip="Принять"
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
    import IconButtonWithProgress from "../../../IconButtonWithProgress";

    export default {
        components: {IconButtonWithProgress},
        data() {
            return {
                headers: [
                    {text: 'Имя', sortable: false},
                    {text: 'Дополнительная комиссия', sortable: false, align: 'center'},
                    {text: '', sortable: false},
                ],
                items: [],
                listing: null,
                rejectForm: null,
                rejectingId: null,
                acceptForm: null,
                acceptingId: null,
            }
        },
        created(){
            this.listing = this.$ewll.initListingForm(this, {
                url: '/crud/agentOffer',
                sort: {id: 'desc'},
                success: function (response) {
                    this.items = response.body.items;
                }.bind(this),
            });
            this.rejectForm = this.$ewll.initForm(this, {
                isFormHandleValidationErrors: false,
                snackSuccessMessage: 'Предложение отклонено',
                success: function () {
                    this.loadItems();
                }.bind(this),
            });
            this.acceptForm = this.$ewll.initForm(this, {
                isFormHandleValidationErrors: false,
                snackSuccessMessage: 'Предложение принято',
                success: function () {
                    this.loadItems();
                }.bind(this),
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
            reject(item) {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Отклонить предложение',
                    body: 'Отклонить предложение пользователя ' + item.sellerName,
                }).then(confirmation => {
                    if (confirmation) {
                        this.rejectingId = item.id;
                        this.rejectForm.submit({url: '/crud/agentOffer/' + item.id + '/reject'});
                    }
                });
            },
            accept(item) {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Принять предложение',
                    body: 'Принять предложение пользователя ' + item.sellerName,
                }).then(confirmation => {
                    if (confirmation) {
                        this.acceptingId = item.id;
                        this.acceptForm.submit({url: '/crud/agentOffer/' + item.id + '/accept'});
                    }
                });
            },
        }
    }
</script>

<style>
</style>
