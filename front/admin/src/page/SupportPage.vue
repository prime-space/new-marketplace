<template>
    <div class="supportPage">
        <card-loading v-if="!unitConfig || !createFormDefinition"/>
        <div v-else>
            <v-data-table :headers="headers"
                          :items="items"
                          :items-per-page="listing.pagination.itemsPerPage"
                          :loading="listing.loading"
                          :mobile-breakpoint="100"
                          class="list__table elevation-1"
                          hide-default-footer
            >
                <template v-slot:top>
                    <filters v-model="filters" :data="filtersConfig" @find="loadItems">
                        <template v-slot:right>
                            <span style="margin-left:15px">
                                <ticket-create-dialog :form-definition="createFormDefinition" @created="read"/>
                            </span>
                        </template>
                    </filters>
                </template>
                <template slot="item" slot-scope="props">
                    <tr class="listTable__row">
                        <td class="table_td__fitContent">{{ props.item.id }}</td>
                        <td>{{ props.item.subject }}</td>
                        <td class="table_td__fitContent text-center">{{ props.item.lastMessageData }}</td>
                        <td class="table_td__fitContent">
                            <v-layout justify-end>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn v-on="on"
                                               @click="read(props.item.id)"
                                               :color="props.item.hasUnreadMessage?'primary':'none'"
                                               text tile icon light
                                        >
                                            <v-icon>mdi-forum</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Перейти</span>
                                </v-tooltip>
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
    </div>
</template>

<script>
    import {page} from './../mixin/page';
    import CardLoading from "./../component/CardLoading";
    import Filters from "./../component/Filters";
    import TicketCreateDialog from "../component/support/TicketCreateDialog";

    export default {
        mixins: [page],
        components: {TicketCreateDialog, CardLoading, Filters,},
        data() {
            return {
                unitConfig: null,
                filters: {},
                headers: [
                    {text: '#', sortable: false},
                    {text: 'Тема', sortable: false,},
                    {text: 'Последнее сообщение', sortable: false, align: 'center'},
                    {text: '', sortable: false},
                ],
                items: [],
                listing: null,
                createFormDefinition: null,
            }
        },
        computed: {
            filtersConfig() {
                if (undefined !== this.unitConfig.read) {
                    return this.unitConfig.read.filters;
                }
                return [];
            }
        },
        created() {
            this.$ewll.initForm(this, {
                method: 'get',
                url: `/crud/ticket/config`,
                success: function (response) {
                    this.unitConfig = response.body;
                    this.listing = this.$ewll.initListingForm(this, {
                        url: '/crud/ticket',
                        sort: {id: 'desc'},
                        success: function (response) {
                            this.items = response.body.items;
                        }.bind(this),
                    });
                    this.loadItems();
                }.bind(this),
            }).submit();
            this.$ewll.initForm(this, {
                method: 'get',
                url: `/crud/ticket/form`,
                success: function (response) {
                    this.createFormDefinition = response.body;
                }.bind(this),
            }).submit();
        },
        methods: {
            loadItems() {
                this.items = [];
                this.listing.submit({filters: this.filters});
            },
            read(ticketId) {
                this.$router.push({name: 'supportTicket', params: {ticketId: ticketId}});
            }
        }
    }
</script>

<style>
</style>
