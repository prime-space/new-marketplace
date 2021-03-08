<template>
    <div class="ordersPage">
        <card-loading v-if="!unitConfig"/>
        <div v-else>
            <v-data-table
                    :headers="headers"
                    :items="items"
                    :items-per-page="listing.pagination.itemsPerPage"
                    :loading="listing.loading"
                    :mobile-breakpoint="100"
                    class="elevation-1"
                    hide-default-footer
            >
                <template v-slot:top>
                    <filters v-model="filters" :data="filtersConfig" @find="loadItems"/>
                </template>
                <template slot="item" slot-scope="props">
                    <tr>
                        <td class="text-center">{{ props.item.id }}</td>
                        <td class="text-center">{{ props.item.cartId }}</td>
                        <td>{{ props.item.productName }}</td>
                        <td class="text-center">{{ props.item.price }}{{props.item.currency}}</td>
                        <td class="text-center">{{ props.item.dateCreate }}</td>
                        <td>
                            <v-layout justify-end>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on: tooltip }">
                                        <v-btn v-on="{ ...tooltip }"
                                                :to="{name: 'order', params: {id: props.item.id}}"
                                                target="_blank"
                                                text
                                                tile
                                                icon
                                        >
                                            <v-icon>mdi-arrow-right-bold-circle-outline</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Открыть в новой вкладке</span>
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
    import {page} from './../../mixin/page';
    import CardLoading from "../../component/CardLoading";
    import Filters from "../../component/Filters";

    export default {
        mixins: [page],
        components: {CardLoading, Filters},
        data() {
            return {
                config: config,
                unitConfig: null,
                filters: {},
                headers: [
                    {text: 'Номер заказа', sortable: false, align: 'center'},
                    {text: 'Номер счета', sortable: false, align: 'center'},
                    {text: 'Товар', sortable: false},
                    {text: 'Оплачено', sortable: false, align: 'center'},
                    {text: 'Дата', sortable: false, align: 'center'},
                    {text: '', sortable: false},
                ],
                items: [],
                listing: null,
            }
        },
        created() {
            this.$ewll.initForm(this, {
                method: 'get',
                url: `/crud/order/config`,
                success: function (response) {
                    this.unitConfig = response.body;
                    this.listing = this.$ewll.initListingForm(this, {
                        url: '/crud/order',
                        sort: {id: 'desc'},
                        success: function (response) {
                            this.items = response.body.items;
                        }.bind(this),
                    });
                }.bind(this),
            }).submit();
        },
        computed: {
            filtersConfig() {
                if (undefined !== this.unitConfig.read) {
                    return this.unitConfig.read.filters;
                }
                return [];
            }
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
