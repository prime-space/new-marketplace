<template>
    <div class="payoutsPage">
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
                                <payout-create-dialog :form-definition="createFormDefinition" @created="loadItems"/>
                            </span>
                        </template>
                    </filters>
                </template>
                <template slot="item" slot-scope="props">
                    <tr class="listTable__row">
                        <td>{{ props.item.id }}</td>
                        <td>{{ props.item.methodName }}</td>
                        <td>{{ props.item.receiver }}</td>
                        <td class="text-center">{{ props.item.amount }} {{ props.item.currency }}</td>
                        <td class="text-center">
                            {{ props.item.writeOff }} {{ props.item.currency }} ({{props.item.fee}}%)
                        </td>
                        <td>{{ props.item.status }}</td>
                        <td>{{ props.item.dateCreate }}</td>
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
    import PayoutCreateDialog from "./../component/payout/PayoutCreateDialog";

    export default {
        mixins: [page],
        components: {CardLoading, Filters, PayoutCreateDialog,},
        data () {
            return {
                unitConfig: null,
                filters: {},
                headers: [
                    {text: '#', sortable: false},
                    {text: 'Направление', sortable: false},
                    {text: 'Получатель', sortable: false},
                    {text: 'Сумма', sortable: false, align: 'center'},
                    {text: 'Списание(комиссия)', sortable: false, align: 'center'},
                    {text: 'Статус', sortable: false},
                    {text: 'Дата', sortable: false},
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
                url: `/crud/payout/config`,
                success: function (response) {
                    this.unitConfig = response.body;
                    this.listing = this.$ewll.initListingForm(this, {
                        url: '/crud/payout',
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
                url: `/crud/payout/form`,
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
        }
    }
</script>

<style>
</style>
