<template>
    <div class="productEditPage">
        <page-tab :tabs="[{hash: 'main', name: 'Основное'},{hash: 'images', name: 'Изображения'},]">
            <template v-slot:default="slotProps">
                <card-loading v-if="!form || sendToVerificationForm.loading"/>
                <v-form v-else @submit.prevent="submit" style="display:contents">
                    <v-card style="display:inline-block;width:100%;overflow:auto">
                        <v-card-text>
                            <v-alert v-if="form.data.statusId === 4" type="warning">
                                Товар отклонен по следующей причине:
                                <div style="white-space:pre-line" class="font-italic">
                                    {{form.data.verificationRejectReason}}
                                </div>
                                Вы можете исправить все проблемы и отправить его на проверку повторно.
                            </v-alert>
                            <v-alert v-if="form.data.statusId === 8" type="error">
                                Товар заблокирован по следующей причине:
                                <div style="white-space:pre-line" class="font-italic">
                                    {{form.data.verificationRejectReason}}
                                </div>
                            </v-alert>


                            <v-tab-item value="main">
                                <common-tab v-if="slotProps.tab==='main'" :form="form"/>
                            </v-tab-item>
                            <v-tab-item value="images">
                                <images-tab v-if="slotProps.tab==='images'" :form="form"/>
                            </v-tab-item>


                        </v-card-text>
                        <v-card-actions>
                            <v-spacer/>
                            <v-btn type="submit" :disabled="form.loading" color="primary">
                                Сохранить
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-form>
            </template>
        </page-tab>
    </div>
</template>

<script>
    import {page} from './../../mixin/page';
    import CardLoading from './../../component/CardLoading';
    import PageTab from './../../component/PageTab';
    import CommonTab from './../../component/product/edit/CommonTab';
    import ImagesTab from './../../component/product/edit/ImagesTab';

    export default {
        mixins: [page],
        components: {CardLoading, PageTab, CommonTab, ImagesTab},
        data() {
            return {
                config: config,
                form: null,
                sendToVerificationForm: null,
            }
        },
        created() {
            this.sendToVerificationForm = this.$ewll.initForm(this, {
                isFormHandleValidationErrors: false,
                snackSuccessMessage: 'Отправлено на проверку',
                success: function () {
                    this.redirect();
                }.bind(this),
            });
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
                this.$ewll.initForm(this, {
                    method: 'get',
                    url: '/crud/product/' + this.$route.params.id + '/form',
                    success: function (response) {
                        this.form = this.$ewll.initForm(this, {
                            url: '/crud/product/' + this.$route.params.id,
                            definition: response.body,
                            data: response.body.data,
                            snackSuccessMessage: 'Сохранено',
                            success: function () {
                                if ([1, 2, 4].includes(this.form.data.statusId)) {
                                    this.$store.dispatch('confirmer/ask', {
                                        title: 'Отправить товар на проверку',
                                        body: 'Пожалуйста, проверьте, что товар соответствует всем правилам сервиса!',
                                    }).then(confirmation => {
                                        if (confirmation) {
                                            this.sendToVerificationForm.submit({url: '/crud/product/' + this.$route.params.id + '/sendToVerification'});
                                        }
                                    });
                                } else {
                                    this.redirect();
                                }
                                this.$store.commit('unsaved/setSaved');
                            }.bind(this)
                        });
                    }.bind(this),
                }).submit();
            },
            submit() {
                if ([5, 6, 7].includes(this.form.data.statusId)) {
                    this.$store.dispatch('confirmer/ask', {
                        title: 'Предупреждение',
                        body: 'После сохранения товар будет снят с продажи и отправлен на повторную проверку',
                    }).then(confirmation => {
                        if (confirmation) {
                            this.form.submit();
                        }
                    });
                } else {
                    this.form.submit();
                }
            },
            redirect() {
                if ([1, 2].includes(this.form.data.statusId)) {
                    this.$router.push({name: 'productFill', params: {id: this.$route.params.id}});
                } else {
                    this.$router.push({name: 'products'});
                }
            },
        }
    }
</script>
