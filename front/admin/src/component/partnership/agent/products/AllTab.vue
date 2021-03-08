<template>
    <div class="partnershipAgentProductsAllTab">
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
                    <filters v-model="filters" :data="unitConfig.read.filters" @find="loadItems"/>
                </template>
                <template slot="item" slot-scope="props">
                    <tr class="listTable__row">
                        <td>{{ props.item.id }}</td>
                        <td>{{ props.item.sellerName }}</td>
                        <td>{{ props.item.name }}</td>
                        <td class="text-center">{{ props.item.salesNum }}</td>
                        <td class="text-center">{{ props.item.priceView }} {{ props.item.currencyView }}</td>
                        <td class="text-center">{{ props.item.partnershipFee }}%</td>
                        <td>
                            <v-layout justify-end>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn v-on="on"
                                               v-clipboard="props.item.url"
                                               @click="onUrlCopy"
                                               text
                                               tile
                                               icon
                                        >
                                            <v-icon>mdi-link</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Скопировать партнерскую ссылку</span>
                                </v-tooltip>

                                <v-menu offset-y left>
                                    <template v-slot:activator="{ on: menu }">
                                        <icon-button-with-progress
                                                :loading="addToGroupForm.loading"
                                                :progressColor="addingId === props.item.id? 'primary' : 'grey'"
                                                icon="mdi-group"
                                                tooltip="Добавить в группу"
                                                :on="menu"
                                        />
                                    </template>
                                    <v-list dense>
                                        <v-subheader
                                                v-if="getAddingGroupList(props.item).length === 0">
                                            Создайте новую группу
                                        </v-subheader>
                                        <div v-else>
                                            <v-subheader>Добавить в группу</v-subheader>
                                            <v-list-item
                                                    v-for="(item, i) in getAddingGroupList(props.item)"
                                                    :key="i"
                                                    @click="addToGroup(props.item.id, item.value)"
                                            >
                                                <v-list-item-icon style="margin-right:10px">
                                                    <v-icon>mdi-plus</v-icon>
                                                </v-list-item-icon>
                                                <v-list-item-title>{{ item.text }}</v-list-item-title>
                                            </v-list-item>
                                        </div>
                                    </v-list>
                                </v-menu>

                                <v-menu offset-y left>
                                    <template v-slot:activator="{ on: menu }">
                                        <icon-button-with-progress
                                                :loading="removeFromGroupForm.loading"
                                                :progressColor="removingId === props.item.id? 'primary' : 'grey'"
                                                icon="mdi-ungroup"
                                                tooltip="Удалить из группы"
                                                :on="menu"
                                                :disabled="props.item.groups.length===0"
                                        />
                                    </template>
                                    <v-list dense>
                                        <v-subheader>Удалить из группы</v-subheader>
                                        <v-list-item
                                                v-for="(item, i) in getRemovingGroupList(props.item)"
                                                :key="i"
                                                @click="removeFromGroup(props.item.id, item.value)"
                                        >
                                            <v-list-item-icon style="margin-right:10px">
                                                <v-icon>mdi-minus</v-icon>
                                            </v-list-item-icon>
                                            <v-list-item-title>{{ item.text }}</v-list-item-title>
                                        </v-list-item>
                                    </v-list>
                                </v-menu>
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
    import IconButtonWithProgress from './../../../../component/IconButtonWithProgress';

    export default {
        components: {CardLoading, Filters, IconButtonWithProgress},
        data() {
            return {
                unitConfig: null,
                filters: {},
                headers: [
                    {text: 'ID', sortable: false},
                    {text: 'Продавец', sortable: false},
                    {text: 'Название', sortable: false,},
                    {text: 'Продаж', sortable: false, align: 'center'},
                    {text: 'Цена', sortable: false, align: 'center'},
                    {text: 'Вознаграждение', sortable: false, align: 'center'},
                    {text: '', sortable: false},
                ],
                items: [],
                listing: null,
                addToGroupForm: null,
                addingId: null,
                removeFromGroupForm: null,
                removingId: null,
            }
        },
        created() {
            this.addToGroupForm = this.$ewll.initForm(this, {
                url: '/crud/partnershipAgentProduct_ProductGroup',
                isFormHandleValidationErrors: false,
                snackSuccessMessage: 'Добавлено',
                success: function () {
                    this.loadItems();
                }.bind(this),
            });
            this.removeFromGroupForm = this.$ewll.initForm(this, {
                method: 'delete',
                isFormHandleValidationErrors: false,
                snackSuccessMessage: 'Удалено',
                success: function () {
                    this.loadItems();
                }.bind(this),
            });
        },
        mounted() {
            this.$ewll.initForm(this, {
                method: 'get',
                url: `/crud/partnershipAgentProduct/config`,
                success: function (response) {
                    this.listing = this.$ewll.initListingForm(this, {
                        url: '/crud/partnershipAgentProduct',
                        sort: {salesNum: 'desc'},
                        success: function (response) {
                            this.items = response.body.items;
                        }.bind(this),
                    });
                    this.unitConfig = response.body;
                }.bind(this),
            }).submit();
        },
        methods: {
            loadItems() {
                this.items = [];
                this.listing.submit({filters: this.filters});
            },
            onUrlCopy() {
                this.$snack.success({text: 'Скопировано'});
            },
            getProductGroupIds(product) {
                let ids = [];
                for (let i in product.groups) {
                    ids.push(product.groups[i].groupId);
                }

                return ids;
            },
            getAddingGroupList(product) {
                return this.unitConfig.read.filters.fields.groupId.choices.filter(item => !this.getProductGroupIds(product).includes(item.value - 0));
            },
            addToGroup(productId, productGroupId) {
                this.addingId = productId;
                this.addToGroupForm.submit({data: {productId, productGroupId}});
            },
            getGroupNameById(groupId) {
                for (let i in this.unitConfig.read.filters.fields.groupId.choices) {
                    let choice = this.unitConfig.read.filters.fields.groupId.choices[i];
                    if (choice.value - 0 === groupId) {
                        return choice.text;
                    }
                }
                //@TODO sentry
                return null;
            },
            getRemovingGroupList(product) {
                let views = [];
                for (let i in product.groups) {
                    let group = product.groups[i];
                    views.push({text: this.getGroupNameById(group.groupId), value: group.id});
                }
                return views;
            },
            removeFromGroup(productId, relationId) {
                this.removingId = productId;
                this.removeFromGroupForm.submit({url: '/crud/partnershipAgentProduct_ProductGroup/' + relationId});
            },
        }
    }
</script>

<style>
</style>
