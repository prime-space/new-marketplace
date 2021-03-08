<template>
    <div class="partnershipSellerSearchTab">
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
                    <td>{{ props.item.name }}</td>
                    <td class="text-center">{{ props.item.agentRating }}</td>
                    <td class="text-center">{{ props.item.agentPartnershipsNum }}</td>
                    <td class="text-center">{{ props.item.agentSalesNum }}</td>
                    <td class="text-center">{{ props.item.createdDate }}</td>
                    <td>
                        <v-layout justify-end>
                            <offer-partnership :id="props.item.id" @offered="loadItems"/>
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
    import OfferPartnership from './OfferPartnership';

    export default {
        components: {OfferPartnership},
        data() {
            return {
                headers: [
                    {text: 'Имя', sortable: false},
                    {text: 'Рейтинг', sortable: false, align: 'center'},
                    {text: 'Продавцов', sortable: false, align: 'center'},
                    {text: 'Продаж', sortable: false, align: 'center'},
                    {text: 'Зарегистрирован', sortable: false, align: 'center'},
                    {text: '', sortable: false},
                ],
                items: [],
                listing: null,
            }
        },
        created() {
            this.listing = this.$ewll.initListingForm(this, {
                url: '/crud/partnershipSellerSearchAgent',
                sort: {id: 'desc'},
                success: function (response) {
                    this.items = response.body.items;
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
        }
    }
</script>

<style>
</style>
