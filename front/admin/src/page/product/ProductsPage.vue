<template>
    <div class="productsPage">
        <card-loading v-if="!unitConfig"/>
        <div v-else>
            <v-data-table
                    :headers="headers"
                    :items="items"
                    :items-per-page="listing.pagination.itemsPerPage"
                    :loading="listing.loading"
                    :mobile-breakpoint="100"
                    class="productsPage__table elevation-1"
                    hide-default-footer
            >
                <template v-slot:top>
                    <filters v-model="filters" :data="filtersConfig" @find="loadItems">
                        <template v-slot:right>
                            <span style="margin-left:15px"><product-create-dialog/></span>
                        </template>
                    </filters>
                </template>
                <template slot="item" slot-scope="props">
                    <tr class="productsPage__row">
                        <td class="productsPage__itemImage">
                            <v-tooltip v-if="props.item.image" content-class="productsPage__imageTooltip" bottom>
                                <template v-slot:activator="{ on }"><img v-on="on"
                                                                         :src="config.cdn+'/'+props.item.image"
                                                                         :alt="props.item.name"
                                                                         class="productsPage__image"></template>
                                <img :src="config.cdn+'/'+props.item.image" :alt="props.item.name"/>
                            </v-tooltip>
                            <div v-else class="productsPage__imageStub">
                                <v-icon large>mdi-image-filter-hdr</v-icon>
                            </div>
                        </td>
                        <td class="text-center">{{ props.item.id }}</td>
                        <td>
                            <span class="caption">{{ props.item.productCategoryName }}</span>
                            <br>
                            {{ props.item.name }}
                        </td>
                        <td>{{ props.item.priceView }} {{ props.item.currency }}</td>
                        <td>{{ props.item.status }}</td>
                        <td class="text-center">{{ props.item.salesNum }}</td>
                        <td class="text-center">{{ props.item.inStockNum }}</td>
                        <td>
                            <v-layout justify-end>
                                <v-tooltip v-if="[5,7].includes(props.item.statusId)" bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn v-on="on"
                                               :href="props.item.urlPage"
                                               target="_blank"
                                               text
                                               tile
                                               icon
                                        >
                                            <v-icon>mdi-open-in-new</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Перейти на страницу товара</span>
                                </v-tooltip>
                                <v-tooltip v-if="[5,7].includes(props.item.statusId)" bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn v-on="on"
                                               v-clipboard="props.item.urlAddToCart"
                                               @click="onUrlCopy"
                                               text
                                               tile
                                               icon
                                        >
                                            <v-icon>mdi-link</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Скопировать ссылку быстрой покупки</span>
                                </v-tooltip>
                                <icon-button-with-progress
                                        v-if="[2,4].includes(props.item.statusId)"
                                        @action="sendToVerification(props.item.id)"
                                        :loading="sendToVerificationForm.loading"
                                        :progressColor="sendingToVerificationId === props.item.id? 'primary' : 'grey'"
                                        icon="mdi-eye"
                                        tooltip="Отправить на проверку"
                                />
                                <icon-button-with-progress
                                        v-if="[5,7].includes(props.item.statusId)"
                                        @action="discontinuing(props.item.id)"
                                        :loading="discontinuingForm.loading"
                                        :progressColor="discontinuingId === props.item.id? 'primary' : 'grey'"
                                        icon="mdi-pause"
                                        tooltip="Снять с продажи"
                                />
                                <icon-button-with-progress
                                        v-if="[6].includes(props.item.statusId)"
                                        @action="continuing(props.item.id)"
                                        :loading="continuingForm.loading"
                                        :progressColor="continuingId === props.item.id? 'primary' : 'grey'"
                                        icon="mdi-play"
                                        tooltip="Вернуть в продажу"
                                />
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn v-on="on"
                                               :to="{name: 'productFill', params: {id: props.item.id}}"
                                               text
                                               tile
                                               icon
                                        >
                                            <v-icon>mdi-basket-fill</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Наполнить</span>
                                </v-tooltip>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn v-on="on"
                                               :to="{name: 'productEdit', params: {id: props.item.id}}"
                                               text
                                               tile
                                               icon
                                        >
                                            <v-icon>mdi-pencil</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Редактировать</span>
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
    import {page} from './../../mixin/page';
    import CardLoading from "../../component/CardLoading";
    import ProductCreateDialog from './../../component/ProductCreateDialog';
    import IconButtonWithProgress from './../../component/IconButtonWithProgress';
    import Filters from "../../component/Filters";

    export default {
        mixins: [page],
        components: {CardLoading, Filters, ProductCreateDialog, IconButtonWithProgress},
        data() {
            return {
                config: config,
                unitConfig: null,
                filters: {},
                headers: [
                    {text: 'Изображение', sortable: false, class: 'productsPage__headerImage'},
                    {text: 'ID', sortable: false, align: 'center'},
                    {text: 'Название', sortable: false},
                    // {text: 'Категория', sortable: false},
                    {text: 'Цена', sortable: false},
                    {text: 'Статус', sortable: false},
                    {text: 'Продаж', sortable: false, align: 'center'},
                    {text: 'В наличии', sortable: false, align: 'center'},
                    {text: '', sortable: false},
                ],
                items: [],
                listing: null,
                removeForm: null,
                removingId: null,
                sendToVerificationForm: null,
                sendingToVerificationId: null,
                discontinuingForm: null,
                discontinuingId: null,
                continuingForm: null,
                continuingId: null,
            }
        },
        created() {
            this.$ewll.initForm(this, {
                method: 'get',
                url: `/crud/product/config`,
                success: function (response) {
                    this.unitConfig = response.body;
                    this.listing = this.$ewll.initListingForm(this, {
                        url: '/crud/product',
                        sort: {id: 'asc'},
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
            this.sendToVerificationForm = this.$ewll.initForm(this, {
                isFormHandleValidationErrors: false,
                success: function () {
                    this.loadItems();
                }.bind(this),
            });
            this.discontinuingForm = this.$ewll.initForm(this, {
                isFormHandleValidationErrors: false,
                success: function () {
                    this.loadItems();
                }.bind(this),
            });
            this.continuingForm = this.$ewll.initForm(this, {
                isFormHandleValidationErrors: false,
                success: function () {
                    this.loadItems();
                }.bind(this),
            });
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
            remove(id) {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Удаление товара #' + id,
                    body: 'Уверены, что хотите продолжить?',
                })
                    .then(confirmation => {
                        if (confirmation) {
                            this.removingId = id;
                            this.removeForm.submit({url: '/crud/product/' + id});
                        }
                    });
            },
            sendToVerification(id) {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Отправить на проверку товар #' + id,
                    body: 'Пожалуйста, проверьте, что товар соответствует всем правилам сервиса!',
                }).then(confirmation => {
                    if (confirmation) {
                        this.sendingToVerificationId = id;
                        this.sendToVerificationForm.submit({url: '/crud/product/' + id + '/sendToVerification'});
                    }
                });
            },
            discontinuing(id) {
                this.discontinuingId = id;
                this.discontinuingForm.submit({url: '/crud/product/' + id + '/discontinuing'});
            },
            continuing(id) {
                this.continuingId = id;
                this.continuingForm.submit({url: '/crud/product/' + id + '/continuing'});
            },
            onUrlCopy() {
                this.$snack.success({text: 'Скопировано'});
            },
        }
    }
</script>

<style>
    .productsPage__row:hover {
        background: inherit !important;
    }

    .productsPage__row {
        height: 70px;
    }

    .productsPage__itemImage {
        padding: 0 10px !important;
        line-height: 0;
        width: 160px;
        height: 70px;
    }

    .productsPage__image {
        height: 80px;
        border-radius: 10px;
        padding: 5px;
    }

    .productsPage__imageTooltip {
        background-color: transparent !important;
    }

    .productsPage__imageStub {
        height: 70%;
        /*border: 1px solid #ccc;*/
        display: flex;
        align-items: center;
        justify-content: center;;
    }

    .productsPage__imageStub icon {
        display: inline-flex;
    }

    @media screen and (max-width: 576px) {
        .productsPage__row {
            height: auto;
        }

        .productsPage__headerImage {
            display: none;
        }

        .productsPage__itemImage {
            display: none;
        }
    }
</style>
