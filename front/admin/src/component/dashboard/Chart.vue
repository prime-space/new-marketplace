<template>
    <div>
        <v-radio-group hide-details row v-model="switchModel" @click.native="fillData()">
            <v-radio label="По месяцам" :value="true"></v-radio>
            <v-radio label="По дням" :value="false"></v-radio>
        </v-radio-group>
        <chart v-if="dataCollection !== null" :chart-data="dataCollection"></chart>
    </div>
</template>

<script>
    import Chart from './chart.js';

    export default {
        components: {
            Chart
        },
        props: ['data'],
        data() {
            return {
                dataCollection: null,
                switchModel: false,
                apiChartData: null
            }
        },
        mounted() {
            this.fillData();
        },
        methods: {
            fillData() {
                this.dataCollection = {
                    labels: this.dataForRender.intervals,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Продаж',
                            yAxisID: "y-axis-0",
                            // backgroundColor: this.$vuetify.theme.currentTheme.accent,
                            backgroundColor: "rgba(1, 119, 253, 0.7)",
                            data: this.dataForRender.sellerSalesNum
                        }, {
                            type: 'bar',
                            label: 'Партнерских продаж',
                            yAxisID: "y-axis-0",
                            // backgroundColor: this.$vuetify.theme.currentTheme.success,
                            backgroundColor: "rgba(63, 81, 181, 0.7)",
                            data: this.dataForRender.partnerSalesNum
                        },
                        {
                            type: 'line',
                            label: 'Сумма',
                            yAxisID: "y-axis-1",
                            // backgroundColor: this.$vuetify.theme.currentTheme.primary,
                            backgroundColor: "rgba(69,170,242, 0.5)",
                            data: this.dataForRender.amount
                        },
                    ]
                };
            },
        },
        computed: {
            dataForRender() {
                if (this.switchModel) {
                    return this.data.byMonths;
                } else {
                    return this.data.byDays;
                }
            }
        }
    }
</script>

<style>
    .noMarginBottomVRadio {
        margin-bottom: 0 !important;
    }
</style>
