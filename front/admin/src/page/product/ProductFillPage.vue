<template>
    <div class="productFillPage">
        <page-tab :tabs="tabsComputed" :invisible="!isMultiCollection">
            <v-tab-item value="in-stock">
                <card-loading v-if="!form"/>
                <v-form v-else @submit.prevent="form.submit" style="display:contents">
                    <div style="display:flex">
                        <v-spacer/>
                    </div>
                    <v-card>
                        <v-container fluid>
                            <v-row dense>
                                <v-col v-if="isMultiCollection" cols="2" style="min-width:140px">
                                    <v-card elevation="0">
                                        <text-dialog @onUploaded="init"/>
                                    </v-card>
                                </v-col>
                                <v-col>
                                    <v-card elevation="0">
                                        <form-type-collection :form="form" path="productObjects">
                                            <v-card v-for="(item,i) in form.data.productObjects"
                                                    :key="i"
                                                    style="margin-bottom:20px"
                                                    outlined
                                            >
                                                <v-card-text>
                                                    <v-textarea v-model="form.data.productObjects[i].data"
                                                                :error-messages="form.getError('productObjects.'+i+'.data')"
                                                                label="Содержимое"
                                                                :rows="isMultiCollection?3:10"
                                                    />
                                                </v-card-text>
                                                <v-btn v-if="i===form.data.productObjects.length-1&&form.data.productObjects.length<form.definition.fields.productObjects.constraints.Count.max"
                                                       color="primary"
                                                       @click="add"
                                                       absolute bottom fab small
                                                >
                                                    <v-icon small>mdi-plus</v-icon>
                                                </v-btn>
                                                <v-btn v-if="form.data.productObjects.length>1"
                                                       color="error"
                                                       @click="$delete(form.data.productObjects,i)"
                                                       absolute bottom right fab small
                                                >
                                                    <v-icon small>mdi-delete</v-icon>
                                                </v-btn>
                                            </v-card>
                                        </form-type-collection>
                                        <div v-if="form.errors.form" class="error--text">{{ form.errors.form }}</div>
                                    </v-card>
                                </v-col>
                            </v-row>
                        </v-container>
                        <v-card-actions>
                            <v-spacer/>
                            <v-btn type="submit" :disabled="form.loading" color="primary">Сохранить</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-form>
            </v-tab-item>
            <v-tab-item value="sold">
            </v-tab-item>
        </page-tab>
    </div>
</template>

<script>
    import {page} from './../../mixin/page';
    import PageTab from './../../component/PageTab';
    import CardLoading from './../../component/CardLoading';
    import FormTypeCollection from './../../form/FormTypeCollection';
    import TextDialog from "../../component/product/fill/TextDialog";

    export default {
        mixins: [page],
        components: {TextDialog, PageTab, CardLoading, FormTypeCollection},
        data() {
            return {
                config: config,
                tab: null,
                form: null,
            }
        },
        mounted() {
            this.init()
        },
        computed: {
            isMultiCollection() {
                let value = false;
                if (null !== this.form && this.form.definition.fields.productObjects.constraints.Count.max > 1) {
                    value = true;
                }

                return value;
            },
            tabsComputed() {
                let tabs = [{hash: 'in-stock', name: 'В наличии'}];
                if (this.isMultiCollection) {
                    tabs.push({hash: 'sold', name: 'Проданные', disabled: true});
                }

                return tabs;
            },
        },
        methods: {
            init() {
                this.form = null;
                this.$ewll.initForm(this, {
                    method: 'get',
                    url: '/crud/product/' + this.$route.params.id + '/objectManipulating/form',
                    success: function (response) {
                        this.form = this.$ewll.initForm(this, {
                            url: '/crud/product/' + this.$route.params.id + '/objectManipulating',
                            definition: response.body,
                            data: response.body.data,
                            snackSuccessMessage: 'Сохранено',
                            success: function () {
                            }.bind(this)
                        });
                        if (0 === this.form.data.productObjects.length) {
                            this.add();
                        }
                    }.bind(this),
                }).submit();
            },
            add() {
                this.form.data.productObjects.push({data: null});
            },
        },
    }
</script>

<style>
</style>
