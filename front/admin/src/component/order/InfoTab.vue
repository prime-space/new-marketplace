<template>
    <div>
        <v-card v-if="item">
            <v-card-title class="headline">Информация</v-card-title>
            <div class="orderPageTopGrid">
                <div class="orderPageTopGrid__item">
                    <v-simple-table>
                        <template v-slot:default>
                            <tbody>
                            <tr>
                                <td>Номер заказа</td>
                                <td>{{ item.id }}</td>
                            </tr>
                            <tr>
                                <td>Номер счета</td>
                                <td>{{ item.cartId }}</td>
                            </tr>
                            <tr>
                                <td>Дата</td>
                                <td>{{ item.dateCreate }}</td>
                            </tr>
                            <tr>
                                <td>Товар</td>
                                <td>
                                    <router-link :to="{name: 'productEdit', params: {id: item.productId}}"
                                                 target="_blank">
                                        {{ item.productName }}
                                    </router-link>
                                </td>
                            </tr>
                            <tr>
                                <td>Количество</td>
                                <td>{{ item.amountInFact }}</td>
                            </tr>
                            <tr>
                                <td>Цена</td>
                                <td>{{ item.productPrice }}{{ item.currency }}</td>
                            </tr>
                            <tr>
                                <td>Оплачено</td>
                                <td>{{ item.price }}{{ item.currency }}</td>
                            </tr>
                            <tr>
                                <td>Email покупателя</td>
                                <td v-if="item.customerBlockedByEmailId === null">
                                    <block-button
                                        :entity-id="item.customerId"
                                        :entity-type-id="customerBlockedEntityTypeIdEmail"
                                        :entity-value="item.maskedCustomerEmail"
                                        text="Блокировать покупателя по email"
                                        @blocked="block"
                                    />
                                </td>
                                <td v-else>
                                    <unblock-button
                                        :blocked-entity-id="item.customerBlockedByEmailId"
                                        :entity-type-id="customerBlockedEntityTypeIdEmail"
                                        :blocked-via="item.maskedCustomerEmail"
                                        text="Разблокировать покупателя по email"
                                        @unblocked="unblock"
                                    />
                                </td>
                            </tr>
                            <tr v-if="item.customerIpId !== null">
                                <td>Ip покупателя</td>
                                <td v-if="item.customerBlockedByIpId === null">
                                    <block-button
                                        :entity-id="item.customerIpId"
                                        :entity-type-id="customerBlockedEntityTypeIdIp"
                                        :entity-value="item.maskedCustomerIp"
                                        @blocked="block"
                                        text="Блокировать покупателя по ip"
                                    />
                                </td>
                                <td v-else>
                                    <unblock-button
                                        :blocked-entity-id="item.customerBlockedByIpId"
                                        :entity-type-id="customerBlockedEntityTypeIdIp"
                                        :blocked-via="item.maskedCustomerIp"
                                        @unblocked="unblock"
                                        text="Разблокировать покупателя по ip"
                                    />
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </div>
                <div class="orderPageTopGrid__item">
                    <table class="orderPage__calculationTable">
                        <tr v-for="(calculation,i) in calculations" :key="i">
                            <td>{{ calculation.a }}</td>
                            <td>{{ calculation.b }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div style="padding:16px">
                <p class="title">Оплаченный товар</p>
                <v-alert v-for="(productObject, i) in item.objects"
                         :key="i"
                         class="orderPage__productObject"
                         type="success"
                         text
                >
                    {{ productObject }}
                </v-alert>
            </div>
        </v-card>
        <card-loading v-else/>
    </div>
</template>

<script>
import CardLoading from "../../component/CardLoading";
import BlockButton from "../../component/order/BlockButton";
import UnblockButton from "@/component/order/UnblockButton";

export default {
    components: {UnblockButton, CardLoading, BlockButton},
    data() {
        return {
            item: null,
            calculations: [],
            customerBlockedEntityTypeIdEmail: 1,
            customerBlockedEntityTypeIdIp: 2,
            calculationNameMap: {
                paid: 'Оплачено',
                systemFee: 'Комиссия системы',
                partnerProfit: 'Вознаграждение партнера',
                total: 'Итого',
            },
        }
    },
    mounted() {
        this.init();
    },
    methods: {
        init() {
            this.$ewll.initForm(this, {
                method: 'get',
                url: `/crud/order/${this.$route.params.id}`,
                success: function (response) {
                    let calculations = response.body.calculations;
                    let currency = response.body.currency;
                    this.addCalculation('paid', this.toFixed(calculations[0].amount) + currency);
                    for (let i in response.body.calculations) {
                        let item = response.body.calculations[i];
                        if (item.name === 'sellerProfit') {
                            continue;
                        }
                        this.addCalculation(item.name, '-' + this.toFixed(item.feeAmount) + currency + ' (' + this.toFixed(item.part) + '%)');
                    }
                    this.addCalculation('total', this.toFixed(calculations[calculations.length - 1].feeAmount) + currency);
                    this.item = response.body;
                }.bind(this),
            }).submit();
        },
        block(entityTypeId, blockedEntityId) {
            if (entityTypeId === this.customerBlockedEntityTypeIdEmail) {
                this.item.customerBlockedByEmailId = blockedEntityId;
            } else {
                if (entityTypeId === this.customerBlockedEntityTypeIdIp) {
                    this.item.customerBlockedByIpId = blockedEntityId;
                }
            }
        },
        unblock(entityTypeId) {
            if (entityTypeId === this.customerBlockedEntityTypeIdEmail) {
                this.item.customerBlockedByEmailId = null;
            } else {
                if (entityTypeId === this.customerBlockedEntityTypeIdIp) {
                    this.item.customerBlockedByIpId = null;
                }
            }
        },
        toFixed(v) {
            v = v - 0;
            return v.toFixed(2);
        },
        addCalculation(name = '', b) {
            this.calculations.push({
                a: this.calculationNameMap[name],
                b: b,
            });
        }
    }
}
</script>

<style lang="scss">
.orderPageTopGrid {
    display: table;
    width: 100%;

    &__item {
        padding: 5px;
        display: table-cell;
        vertical-align: top;
    }
}

.orderPage__calculationTable {
    width: min-content;
    float: right;
}

.orderPage__calculationTable td {
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
    border-right: 1px solid rgba(0, 0, 0, 0.12);
    padding: 7px;
    font-size: 14px;
}

.orderPage__calculationTable td:last-child {
    border-right: none;
}

.orderPage__calculationTable tr:last-child td {
    border-bottom: none;
}

.orderPage__calculationTable tr:last-child td:first-child, .orderPage__calculationTable tr:first-child td:first-child {
    text-align: right;
}

.orderPage__productObject {
    margin-bottom: 8px;
}

@media screen and (max-width: 577px) {
    .orderPageTopGrid {
        display: block;
        width: auto;

        &__item {
            display: block;
        }
    }

    .orderPage__calculationTable {
        float: none;
    }
}
</style>
