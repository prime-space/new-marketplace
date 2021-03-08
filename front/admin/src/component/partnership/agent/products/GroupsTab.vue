<template>
    <div class="partnershipAgentProductsGroupsTab">
        <card-loading v-if="!unitConfig"/>
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
                                <product-group-create-dialog :form-definition="unitConfig.create" @created="loadItems"/>
                            </span>
                        </template>
                    </filters>
                </template>
                <template slot="item" slot-scope="props">
                    <tr class="listTable__row">
                        <td class="table_td__fitContent">{{ props.item.id }}</td>
                        <td>{{ props.item.name }}</td>
                        <td class="table_td__fitContent">{{ props.item.productsNum }}</td>
                        <td class="table_td__fitContent">
                            <v-layout justify-end>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn v-on="on"
                                               :to="{name:'partnershipAgentProducts', hash:'#all', query: {f_groupId: props.item.id}}"
                                               text
                                               tile
                                               icon
                                        >
                                            <v-icon>mdi-dolly</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Товары</span>
                                </v-tooltip>
                                <icon-button-with-progress
                                        @action="remove(props.item.id)"
                                        :loading="removeForm.loading"
                                        :progressColor="removingId === props.item.id? 'primary' : 'grey'"
                                        icon="mdi-delete"
                                        tooltip="Удалить"
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
    </div>
</template>

<script>
    import CardLoading from "../../../CardLoading";
    import Filters from "../../../Filters";
    import ProductGroupCreateDialog from "./ProductGroupCreateDialog";
    import IconButtonWithProgress from './../../../../component/IconButtonWithProgress';

    export default {
        components: {CardLoading, Filters, ProductGroupCreateDialog, IconButtonWithProgress},
        data() {
            return {
                unitConfig: null,
                filters: {},
                headers: [
                    {text: 'ID', sortable: false},
                    {text: 'Название', sortable: false,},
                    {text: 'Кол-во товаров', sortable: false,},
                    {text: '', sortable: false},
                ],
                items: [],
                listing: null,
                removeForm: null,
                removingId: null,
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
                url: `/crud/partnershipAgentProductGroup/config`,
                success: function (response) {
                    this.unitConfig = response.body;
                    this.listing = this.$ewll.initListingForm(this, {
                        url: '/crud/partnershipAgentProductGroup',
                        sort: {id: 'desc'},
                        success: function (response) {
                            this.items = response.body.items;
                        }.bind(this),
                    });
                    this.loadItems();
                }.bind(this),
            }).submit();
            this.removeForm = this.$ewll.initForm(this, {
                method: 'delete',
                success: function () {
                    this.loadItems();
                }.bind(this),
            });
        },
        methods: {
            loadItems() {
                this.items = [];
                this.listing.submit({filters: this.filters});
            },
            remove(id) {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Удаление группы #' + id,
                    body: 'Уверены, что хотите продолжить?',
                })
                    .then(confirmation => {
                        if (confirmation) {
                            this.removingId = id;
                            this.removeForm.submit({url: '/crud/partnershipAgentProductGroup/' + id});
                        }
                    });
            },
        }
    }
</script>

<style>
</style>
