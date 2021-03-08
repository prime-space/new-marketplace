<template>
    <div class="transactionList">
        <card-loading v-if="!unitConfig"/>
        <div v-else>
            <div v-if="account">
                <v-card class="pa-4">
                    <v-card-title style="padding-top:0;padding-left:0">Счета</v-card-title>
                    <account name="Баланс" :id="account.id" :balance="account.balance" :sign="account.currencyView"/>
                    <account name="На удержании" :balance="account.hold" :sign="account.currencyView"/>
                </v-card>
                <br/>
                <v-card class="pa-4">
                    <v-card-title style="padding-top:0;padding-left:0">Статистика продаж</v-card-title>
                    <chart :data="chart"/>
                </v-card>
            </div>
            <br/>
            <v-data-table :headers="headers"
                          :items="items"
                          :items-per-page="listing.pagination.itemsPerPage"
                          :loading="listing.loading"
                          :mobile-breakpoint="100"
                          class="list__table elevation-1"
                          hide-default-footer
            >
                <template v-slot:top>
                    <v-card-title style="padding-bottom:0">Операции по счету</v-card-title>
                    <filters v-model="filters" :data="filtersConfig" @find="loadItems"/>
                </template>
                <template slot="item" slot-scope="props">
                    <tr class="listTable__row">
                        <td>
                            <transaction-description :id="props.item.methodId" :data="props.item.descriptionData"/>
                        </td>
                        <td class="text-center">{{ props.item.amount }}{{props.item.currency}}</td>
                        <td class="text-center">{{ props.item.dateCreate }}</td>
                        <td class="text-center">{{ props.item.applying }}</td>
                        <td></td>
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
    import CardLoading from "./../../component/CardLoading";
    import Filters from "./../../component/Filters";
    import TransactionDescription from "./TransactionDescription";
    import Account from "./Account";
    import Chart from "./Chart";

    export default {
        components: {TransactionDescription, Account, CardLoading, Filters, Chart},
        data() {
            return {
                unitConfig: null,
                filters: {},
                headers: [
                    {text: 'Описание', sortable: false},
                    {text: 'Сумма', sortable: false, align: 'center'},
                    {text: 'Дата', sortable: false, align: 'center'},
                    {text: 'Зачисление', sortable: false, align: 'center'},
                    {text: '', sortable: false},
                ],
                items: [],
                listing: null,
                account: null,
                chart: null,
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
                url: `/crud/transaction/config`,
                success: function (response) {
                    this.unitConfig = response.body;
                    this.listing = this.$ewll.initListingForm(this, {
                        url: '/crud/transaction',
                        sort: {id: 'desc'},
                        success: function (response) {
                            this.items = response.body.items;
                            this.chart = response.body.extra.chart;
                            this.account = response.body.extra.account;
                        }.bind(this),
                    });
                    this.loadItems();
                }.bind(this),
            }).submit();
        },
        methods: {
            loadItems() {
                this.items = [];
                this.listing.submit({filters: this.filters});
            },
        }
    }
</script>

<style>
</style>
