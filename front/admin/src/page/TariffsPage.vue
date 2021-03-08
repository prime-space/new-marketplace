<template>
    <div class="tariffsPage">
        <card-loading v-if="items===null"/>
        <template v-else>
            <template v-for="(item,i) in items">
                <v-card v-if="!item.isHidden || item.id===config.user.tariffId"
                        :key="i"
                        width="344"
                        min-width="344"
                        class="tariffsPage__card"
                        style="margin-top: 20px"
                >
                    <v-list-item class="tariffsPage__card__header">
                        <v-list-item-content>
                            <v-list-item-title class="headline">{{item.name}}</v-list-item-title>
                            <v-list-item-subtitle
                                    :class="['tariffsPage__card__header__current',{'tariffsPage__card__header__current--hidden': item.id!==config.user.tariffId}]"
                            >Текущий тариф
                            </v-list-item-subtitle>
                        </v-list-item-content>
                    </v-list-item>

                    <div style="text-align:center">
                        <v-icon style="font-size:125px" :color="item.id===config.user.tariffId ? 'primary' : 'none'"
                                x-large>{{item.icon}}
                        </v-icon>
                    </div>

                    <v-simple-table class="tariffsPage__card__data">
                        <tbody>
                        <tr>
                            <td class="tariffsPage__card__data__key">Комиссия с продаж</td>
                            <td class="tariffsPage__card__data__value">{{item.saleFee}}%</td>
                        </tr>
                        <tr>
                            <td class="tariffsPage__card__data__key">Удержание средств</td>
                            <td class="tariffsPage__card__data__value">{{item.holdDays}} дня</td>
                        </tr>
                        <tr>
                            <td class="tariffsPage__card__data__key">Стоимость</td>
                            <td class="tariffsPage__card__data__value">{{item.price}} р./мес</td>
                        </tr>
                        </tbody>
                    </v-simple-table>
                </v-card>
            </template>
        </template>
    </div>
</template>

<script>
    import {page} from './../mixin/page';
    import CardLoading from "./../component/CardLoading";

    export default {
        mixins: [page],
        components: {CardLoading},
        data() {
            return {
                config,
                items: null,
                listing: null,
            }
        },
        created() {
            this.listing = this.$ewll.initListingForm(this, {
                url: '/crud/tariff',
                sort: {price: 'asc'},
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
                this.listing.submit();
            },
        }
    }
</script>

<style lang="scss">
    .tariffsPage {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;

        &__card {
            &__header {
                &__current {
                    &--hidden {
                        visibility: hidden;
                    }
                }
            }
            &__data {
                margin-top: 10px;

                &__key {
                    font-size: 16px !important;
                }

                &__value {
                    font-size: 20px !important;
                    text-align: end;
                }
            }
        }
    }
</style>
